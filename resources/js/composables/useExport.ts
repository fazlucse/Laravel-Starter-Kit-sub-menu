// src/composables/useExport.ts
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { ref } from 'vue';
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
        printDelay = 800,
        autoClosePrint = false,
    } = options;

    const isProcessing = ref(false);

    // Helper: get the printable element
    const getContentElement = () => {
        const el = document.getElementById(contentId);
        if (!el) console.warn(`Element #${contentId} not found`);
        return el;
    };

    // 1. PRINT (clean, no shadows)
    // Inside your useExport.ts → print() function
    const print = async () => {
        const content = getContentElement();
        if (!content) return;

        isProcessing.value = true;

        // 1. Clone content to avoid modifying live DOM
        const clone = content.cloneNode(true) as HTMLElement;

        // 2. Fix avatars: Convert all <img> to base64 (solves CORS forever)
        const images = clone.querySelectorAll('img');
        await Promise.all(
            Array.from(images).map(async (img: HTMLImageElement) => {
                const src = img.src;
                if (!src || src.startsWith('data:') || src.startsWith('blob:'))
                    return;

                try {
                    const base64 = await imageToBase64(src);
                    img.src = base64;
                } catch (err) {
                    img.src = '/avatars/default.png'; // fallback
                }
            }),
        );

        const printWindow = window.open('', '_blank', 'width=1200,height=900');

        if (!printWindow) {
            alert('Please allow popups for printing');
            isProcessing.value = false;
            return;
        }

        // 3. CRITICAL: Include your main app.css (the one from import '../css/app.css')
        // This file is usually served at /src/css/app.css or /assets/app.[hash].css
        // We'll grab the actual compiled version from the DOM
        const appCssLink = document.querySelector(
            'link[href*="app.css"], link[href*="/css/app.css"]',
        ) as HTMLLinkElement;
        const tailwindCss = appCssLink
            ? `<link rel="stylesheet" href="${appCssLink.href}" />`
            : // Fallback: if not found, try common Vite paths
              `<link rel="stylesheet" href="/src/css/app.css" />
       <link rel="stylesheet" href="/assets/app.css" />`;

        // Also collect any other styles
        let otherStyles = '';
        document
            .querySelectorAll('link[rel="stylesheet"], style')
            .forEach((el) => {
                if (el !== appCssLink) {
                    if (el.tagName === 'LINK') {
                        otherStyles += `<link rel="stylesheet" href="${(el as HTMLLinkElement).href}" />`;
                    } else {
                        otherStyles += `<style>${(el as HTMLStyleElement).innerHTML}</style>`;
                    }
                }
            });

        // 4. Clean print overrides (keeps Tailwind, removes shadows)
        const printCss = `
    <style>
      @page { margin: 1cm; size: A4 portrait; }
      body { 
        margin: 0; padding: 2rem; 
        font-family: system-ui, -apple-system, sans-serif;
        background: white !important; 
        color: #000 !important; 
      }
      *, *::before, *::after {
        box-shadow: none !important;
        -webkit-box-shadow: none !important;
        text-shadow: none !important;
        border: none !important;
        outline: none !important;
      }
      .shadow, .shadow-*, .ring, .ring-*, .border, .border-* { 
        box-shadow: none !important; 
        border: none !important; 
      }
      img {
        border-radius: 50% !important;
        border: 4px solid #111 !important;
        object-fit: cover !important;
        page-break-inside: avoid;
      }
      #${contentId} { background: white !important; padding: 10px; }
      .print\\:hidden, nav, header, footer, button, .noprint { display: none !important; }
    </style>
  `;

        printWindow.document.write(`
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="utf-8">
      <title>${title}</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      
      <!-- MAIN TAILWIND + APP.CSS -->
      ${tailwindCss}
      ${otherStyles}
      
      <!-- CLEAN PRINT STYLES -->
      ${printCss}
    </head>
    <body>
      <div class="max-w-4xl mx-auto bg-white">
        ${clone.outerHTML}
      </div>
    </body>
    </html>
  `);

        printWindow.document.close();
        printWindow.focus();

        printWindow.onload = () => {
            setTimeout(() => {
                printWindow.print();
                if (autoClosePrint) {
                    window.addEventListener(
                        'focus',
                        () => printWindow.close(),
                        { once: true },
                    );
                }
                isProcessing.value = false;
            }, printDelay);
        };
    };

    // Helper function
    const imageToBase64 = (url: string): Promise<string> => {
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
            img.onerror = () => resolve('/avatars/default.png');
            img.src = url;
        });
    };
    // 2. EXPORT TO PDF (using html2canvas + jsPDF → perfect layout)
    const exportToPDF = async () => {
        const content = getContentElement();
        if (!content) return;

        isProcessing.value = true;

        // Temporarily remove shadows/borders for perfect screenshot
        const originalStyle = content.style.cssText;
        content.style.boxShadow = 'none';
        content.style.border = 'none';
        content.style.borderRadius = '0';
        content.style.background = 'white';

        try {
            const canvas = await html2canvas(content, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                logging: false,
            });

            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4',
            });

            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = pdf.internal.pageSize.getHeight();
            const imgWidth = canvas.width;
            const imgHeight = canvas.height;
            const ratio = Math.min(pdfWidth / imgWidth, pdfHeight / imgHeight);
            const imgX = (pdfWidth - imgWidth * ratio) / 2;
            const imgY = 10;

            pdf.addImage(
                imgData,
                'PNG',
                imgX,
                imgY,
                imgWidth * ratio,
                imgHeight * ratio,
            );
            pdf.save(`${filename}.pdf`);
        } catch (err) {
            console.error('PDF export failed:', err);
            alert('PDF export failed. See console.');
        } finally {
            content.style.cssText = originalStyle; // restore
            isProcessing.value = false;
        }
    };

    // 3. EXPORT TO EXCEL (.xlsx) – perfect for employee data, tables, etc.
    const exportToExcel = (excelData: any[] | HTMLElement = []) => {
        isProcessing.value = true;

        let workbookData: any[] = [];

        // Option A: Pass raw data array
        if (Array.isArray(excelData) && excelData.length > 0) {
            workbookData = excelData;
        }
        // Option B: Auto-extract from tables inside #print_content
        else {
            const tables = getContentElement()?.querySelectorAll('table');
            if (tables && tables.length > 0) {
                tables.forEach((table) => {
                    const rows = Array.from(table.rows).map((row) =>
                        Array.from(row.cells).map((cell) =>
                            cell.innerText.trim(),
                        ),
                    );
                    workbookData = workbookData.concat(rows);
                });
            }
        }

        if (workbookData.length === 0) {
            alert('No data found to export to Excel');
            isProcessing.value = false;
            return;
        }

        const ws = XLSX.utils.aoa_to_sheet(workbookData);
        const wb = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(wb, ws, 'Sheet1');
        XLSX.writeFile(wb, `${filename}.xlsx`);

        isProcessing.value = false;
    };

    return {
        isProcessing,
        print,
        exportToPDF,
        exportToExcel,
    };
}
