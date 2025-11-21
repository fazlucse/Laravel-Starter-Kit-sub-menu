// src/composables/useExport.ts
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { readonly, ref } from 'vue';
import * as XLSX from 'xlsx';

interface UseExportOptions {
    contentId?: string;
    filename?: string;
    title?: string;
    printDelay?: number;
    autoClosePrint?: boolean;
}

export function useExport(options: UseExportOptions = {}) {
    const {
        contentId = 'print_content',
        filename = 'document',
        title = 'Document',
        printDelay = 1000,
        autoClosePrint = true,
    } = options;

    const isProcessing = ref(false);

    const getContentElement = () => {
        const el = document.getElementById(contentId);
        if (!el)
            console.warn(`Element with id "${contentId}" not found for export`);
        return el;
    };

    // ──────────────────────────────────────────────────────────────
    // FIX oklch(), lab(), lch() → html2canvas HATES these colors
    // ───────────────────────────────────────────────────────────────
    const applySafeStylesForCanvas = () => {
        const style = document.createElement('style');
        style.id = 'safe-canvas-styles';
        style.textContent = `
      * {
        color: #000 !important;
        background-color: #fff !important;
        border-color: #000 !important;
        box-shadow: none !important;
        text-shadow: none !important;
      }
      img { filter: none !important; opacity: 1 !important; }
      .dark *, .dark { background-color: #fff !important; color: #000 !important; }
    `;
        document.head.appendChild(style);
    };

    const removeSafeStyles = () => {
        document.getElementById('safe-canvas-styles')?.remove();
    };

    // ──────────────────────────────────────────────────────────────
    // Convert image to base64 (avatars, photos)
    // ──────────────────────────────────────────────────────────────
    const imageToBase64 = async (url: string): Promise<string> => {
        if (!url || url.startsWith('data:') || url.startsWith('blob:'))
            return url;

        return new Promise((resolve) => {
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                ctx?.drawImage(img, 0, 0);
                resolve(canvas.toDataURL('image/png'));
            };
            img.onerror = () => resolve('/avatars/default.png'); // fallback
            img.src = url + '?t=' + Date.now();
        });
    };

    // ──────────────────────────────────────────────────────────────
    // 1. PRINT — Beautiful A4 with 10px top/bottom, 5px sides
    // ──────────────────────────────────────────────────────────────
    const print = async () => {
        const content = getContentElement();
        if (!content) return;

        isProcessing.value = true;
        const clone = content.cloneNode(true) as HTMLElement;

        // Fix all images
        const imgs = clone.querySelectorAll('img');
        await Promise.all(
            Array.from(imgs).map((img) =>
                imageToBase64(img.src).then((src) => (img.src = src)),
            ),
        );

        const win = window.open('', '_blank', 'width=1200,height=900');
        if (!win) {
            alert('Please allow popups');
            isProcessing.value = false;
            return;
        }

        // Include all current stylesheets
        const styles = Array.from(
            document.querySelectorAll('link[rel="stylesheet"], style'),
        )
            .map((el) => el.outerHTML)
            .join('\n');

        const printCSS = `
      <style>
        @page { margin: 0; size: A4 portrait; }
        html, body { margin:0; padding:0; background:white; color:black; }
        #${contentId} {
          padding: 10px 5px 10px 5px !important;
          box-sizing: border-box;
          background: white !important;
        }
        *, *::before, *::after {
          box-shadow: none !important;
          border: none !important;
          text-shadow: none !important;
        }
        img {
          border-radius: 50% !important;
          border: 6px solid #111 !important;
          object-fit: cover !important;
        }
        .print\\:hidden, button, nav, header, footer, aside { display: none !important; }
      </style>
    `;

        win.document.write(`
      <!DOCTYPE html>
      <html>
        <head>
          <meta charset="utf-8">
          <title>${title}</title>
          ${styles}
          ${printCSS}
        </head>
        <body>${clone.outerHTML}</body>
      </html>
    `);

        win.document.close();
        win.focus();

        win.onload = () => {
            setTimeout(() => {
                win.print();
                if (autoClosePrint) {
                    window.addEventListener('focus', () => win.close(), {
                        once: true,
                    });
                }
                isProcessing.value = false;
            }, printDelay);
        };
    };

    // ──────────────────────────────────────────────────────────────
    // 2. EXPORT TO PDF — 100% WORKING (no oklch error!)
    // ──────────────────────────────────────────────────────────────
    // 2. EXPORT TO PDF → Opens in new window (preview + download)
    const exportToPDF = async () => {
        const content = getContentElement();
        if (!content) return;

        isProcessing.value = true;

        const originalOverflow = document.body.style.overflow;
        const originalStyle = content.style.cssText;

        try {
            // Apply safe styles (fixes oklch error)
            applySafeStylesForCanvas();
            document.body.style.overflow = 'hidden';
            content.style.background = 'white';
            content.style.padding = '40px';
            content.style.boxShadow = 'none';

            await new Promise((r) => setTimeout(r, 200));

            const canvas = await html2canvas(content, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
                allowTaint: false,
                removeContainer: true,
            });

            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');

            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight() - 20;
            const ratio = Math.min(
                pdfWidth / canvas.width,
                pdfHeight / canvas.height,
            );
            const width = canvas.width * ratio;
            const height = canvas.height * ratio;

            pdf.addImage(
                imgData,
                'PNG',
                (pdfWidth - width) / 2,
                10,
                width,
                height,
            );

            // THIS IS THE MAGIC → Open in new window instead of direct download
            const pdfBlob = pdf.output('blob');
            const pdfUrl = URL.createObjectURL(pdfBlob);

            const previewWindow = window.open(
                pdfUrl,
                '_blank',
                'width=1000,height=800,scrollbars=yes',
            );

            if (!previewWindow) {
                alert('Please allow popups to preview PDF');
                // Fallback: direct download
                pdf.save(`${filename}.pdf`);
            } else {
                previewWindow.focus();
                // Optional: Add download button in preview
                previewWindow.onload = () => {
                    setTimeout(() => {
                        previewWindow.document.title = `${filename}.pdf`;
                    }, 500);
                };
            }
        } catch (err) {
            console.error('PDF Export Failed:', err);
            alert('Failed to generate PDF');
            // Fallback download even if preview fails
            const fallbackPdf = new jsPDF('p', 'mm', 'a4');
            fallbackPdf.text(
                'PDF generation failed. Please try again.',
                20,
                20,
            );
            fallbackPdf.save(`${filename}_error.pdf`);
        } finally {
            removeSafeStyles();
            document.body.style.overflow = originalOverflow;
            content.style.cssText = originalStyle;
            isProcessing.value = false;
        }
    };
    // ──────────────────────────────────────────────────────────────
    // 3. EXPORT TO EXCEL
    // ──────────────────────────────────────────────────────────────
    const exportToExcel = (data: any[] = []) => {
        isProcessing.value = true;

        const sheetData = data.length > 0 ? data : [['No data to export']];
        const ws = XLSX.utils.aoa_to_sheet(sheetData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Employee Data');
        XLSX.writeFile(wb, `${filename}.xlsx`);

        isProcessing.value = false;
    };

    // ──────────────────────────────────────────────────────────────
    // RETURN
    // ──────────────────────────────────────────────────────────────
    return {
        isProcessing: readonly(isProcessing),
        print,
        exportToPDF,
        exportToExcel,
    };
}
