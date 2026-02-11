// src/composables/useExport.ts
import html2canvas from 'html2canvas';
import jsPDF from 'jspdf';
import { ref } from 'vue';
import * as XLSX from 'xlsx';
const companyName = import.meta.env.VITE_COM_NAME || 'Default Company';
const companyAddress = import.meta.env.VITE_COM_ADDRESS || '';

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
  //   const print = async () => {
  //       const content = getContentElement();
  //       if (!content) return;
  //
  //       isProcessing.value = true;
  //
  //       // 1. Clone content to avoid modifying live DOM
  //       const clone = content.cloneNode(true) as HTMLElement;
  //
  //       // 2. Fix avatars: Convert all <img> to base64 (solves CORS forever)
  //       const images = clone.querySelectorAll('img');
  //       await Promise.all(
  //           Array.from(images).map(async (img: HTMLImageElement) => {
  //               const src = img.src;
  //               if (!src || src.startsWith('data:') || src.startsWith('blob:'))
  //                   return;
  //
  //               try {
  //                   const base64 = await imageToBase64(src);
  //                   img.src = base64;
  //               } catch (err) {
  //                   img.src = '/avatars/default.png'; // fallback
  //               }
  //           }),
  //       );
  //
  //       const printWindow = window.open('', '_blank', 'width=1200,height=900');
  //
  //       if (!printWindow) {
  //           //    alert('Please allow popups for printing');
  //           isProcessing.value = false;
  //           return;
  //       }
  //
  //       // 3. CRITICAL: Include your main app.css (the one from import '../css/app.css')
  //       // This file is usually served at /src/css/app.css or /assets/app.[hash].css
  //       // We'll grab the actual compiled version from the DOM
  //       const appCssLink = document.querySelector(
  //           'link[href*="app.css"], link[href*="/css/app.css"]',
  //       ) as HTMLLinkElement;
  //       const tailwindCss = appCssLink
  //           ? `<link rel="stylesheet" href="${appCssLink.href}" />`
  //           : // Fallback: if not found, try common Vite paths
  //             `<link rel="stylesheet" href="/src/css/app.css" />
  //      <link rel="stylesheet" href="/assets/app.css" />`;
  //
  //       // Also collect any other styles
  //       let otherStyles = '';
  //       document
  //           .querySelectorAll('link[rel="stylesheet"], style')
  //           .forEach((el) => {
  //               if (el !== appCssLink) {
  //                   if (el.tagName === 'LINK') {
  //                       otherStyles += `<link rel="stylesheet" href="${(el as HTMLLinkElement).href}" />`;
  //                   } else {
  //                       otherStyles += `<style>${(el as HTMLStyleElement).innerHTML}</style>`;
  //                   }
  //               }
  //           });
  //
  //       // 4. Clean print overrides (keeps Tailwind, removes shadows)
  //       const printCss = `
  //   <style>
  //     @page { margin: 1cm; size: A4 portrait; }
  //
  //     body {
  //       margin: 0; padding: 2rem;
  //       font-family: system-ui, -apple-system, sans-serif;
  //       background: white !important;
  //       color: #000 !important;
  //     }
  //     *, *::before, *::after {
  //       box-shadow: none !important;
  //       -webkit-box-shadow: none !important;
  //       text-shadow: none !important;
  //       border: none !important;
  //       outline: none !important;
  //     }
  //     .shadow, .shadow-*, .ring, .ring-*, .border, .border-* {
  //       box-shadow: none !important;
  //       border: none !important;
  //     }
  //     img {
  //       border-radius: 50% !important;
  //       border: 4px solid #111 !important;
  //       object-fit: cover !important;
  //       page-break-inside: avoid;
  //     }
  //     #${contentId} { background: white !important; padding: 10px; }
  //     .print\\:hidden, nav, header, footer, button, .noprint { display: none !important; }
  //   </style>
  // `;
  //
  //       printWindow.document.write(`
  //   <!DOCTYPE html>
  //   <html lang="en">
  //   <head>
  //     <meta charset="utf-8">
  //     <title>${title}</title>
  //     <meta name="viewport" content="width=device-width, initial-scale=1">
  //
  //     <!-- MAIN TAILWIND + APP.CSS -->
  //     ${tailwindCss}
  //     ${otherStyles}
  //
  //     <!-- CLEAN PRINT STYLES -->
  //     ${printCss}
  //   </head>
  //   <body>
  //     <div class="max-w-4xl mx-auto bg-white">
  //       ${clone.outerHTML}
  //     </div>
  //   </body>
  //   </html>
  // `);
  //
  //       printWindow.document.close();
  //       printWindow.focus();
  //
  //       printWindow.onload = () => {
  //           setTimeout(() => {
  //               printWindow.print();
  //               if (autoClosePrint) {
  //                   window.addEventListener(
  //                       'focus',
  //                       () => printWindow.close(),
  //                       { once: true },
  //                   );
  //               }
  //               isProcessing.value = false;
  //           }, printDelay);
  //       };
  //   };

// Set headerHtml = null as default
    const print = async (contentId: string, headerHtml: string | null = null, title = 'Document') => {
        const printWindow = window.open('', 'PrintWindow', 'width=1200,height=900');
        if (!printWindow) return;

        isProcessing.value = true;
        const content = document.getElementById(contentId);
        if (!content) return;

        let styles = '';
        for (const sheet of document.styleSheets) {
            try { for (const rule of sheet.cssRules) { styles += rule.cssText + '\n'; } } catch (e) {}
        }

        const clone = content.cloneNode(true) as HTMLElement;

        const printStyles = `
    <style>
        ${styles}
        @page { size: A4 portrait; margin: 15mm 10mm; }
        body { margin: 0; padding: 0; background: white !important; -webkit-print-color-adjust: exact !important; }

        .print-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .print-thead { display: table-header-group; }
        .print-tbody { display: table-row-group; }

        /* General Layout Fixes */
        * { word-break: keep-all !important; box-sizing: border-box !important; }
        .grid { display: grid !important; grid-template-columns: 1fr 1fr !important; gap: 20px !important; }
        .flex { display: flex !important; flex-direction: row !important; }
        .min-w-\\[150px\\] { min-width: 150px !important; flex-shrink: 0 !important; }

        .custom-header-wrapper { width: 100%; color: black !important; }
    </style>
    `;

        // Only generate the THEAD if headerHtml is provided
        const repeatingHeader = headerHtml
            ? `<thead class="print-thead">
                <tr>
                    <td>
                        <div class="custom-header-wrapper">${headerHtml}</div>
                    </td>
                </tr>
           </thead>`
            : '';

        printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>${title}</title>
            ${printStyles}
        </head>
        <body>
            <table class="print-table">
                ${repeatingHeader}
                <tbody class="print-tbody">
                    <tr>
                        <td>
                            <div style="padding-top: ${headerHtml ? '20px' : '0px'}; width: 100%;">
                                ${clone.outerHTML}
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </body>
        </html>
    `);

        printWindow.document.close();
        printWindow.onload = () => {
            printWindow.focus();
            setTimeout(() => {
                printWindow.print();
                isProcessing.value = false;
            }, 1000);
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
        //   document.getElementById('safe-canvas-styles')?.remove();
    };
    // 2. EXPORT TO PDF (using html2canvas + jsPDF → perfect layout)
    // const exportToPDF = async () => {
    //     const content = getContentElement();
    //     if (!content) return;

    //     isProcessing.value = true;

    //     const originalOverflow = document.body.style.overflow;
    //     const originalStyle = content.style.cssText;

    //     try {
    //         // Apply safe styles (fixes oklch error)
    //         applySafeStylesForCanvas();
    //         document.body.style.overflow = 'hidden';
    //         content.style.background = 'white';
    //         content.style.padding = '10px';
    //         content.style.boxShadow = 'none';

    //         await new Promise((r) => setTimeout(r, 200));

    //         const canvas = await html2canvas(content, {
    //             scale: 2,
    //             useCORS: true,
    //             backgroundColor: '#ffffff',
    //             logging: false,
    //             allowTaint: false,
    //             removeContainer: true,
    //         });

    //         const imgData = canvas.toDataURL('image/png');
    //         const pdf = new jsPDF('p', 'mm', 'a4');

    //         const pdfWidth = pdf.internal.pageSize.getWidth();
    //         const pdfHeight = pdf.internal.pageSize.getHeight() - 20;
    //         const ratio = Math.min(
    //             pdfWidth / canvas.width,
    //             pdfHeight / canvas.height,
    //         );
    //         const width = canvas.width * ratio;
    //         const height = canvas.height * ratio;

    //         pdf.addImage(
    //             imgData,
    //             'PNG',
    //             (pdfWidth - width) / 2,
    //             10,
    //             width,
    //             height,
    //         );

    //         // THIS IS THE MAGIC → Open in new window instead of direct download
    //         const pdfBlob = pdf.output('blob');
    //         const pdfUrl = URL.createObjectURL(pdfBlob);

    //         const previewWindow = window.open(
    //             pdfUrl,
    //             '_blank',
    //             'width=1000,height=800,scrollbars=yes',
    //         );

    //         if (!previewWindow) {
    //             alert('Please allow popups to preview PDF');
    //             // Fallback: direct download
    //             pdf.save(`${filename}.pdf`);
    //         } else {
    //             previewWindow.focus();
    //             // Optional: Add download button in preview
    //             previewWindow.onload = () => {
    //                 setTimeout(() => {
    //                     previewWindow.document.title = `${filename}.pdf`;
    //                 }, 500);
    //             };
    //         }
    //     } catch (err) {
    //         console.error('PDF Export Failed:', err);
    //         alert('Failed to generate PDF');
    //         // Fallback download even if preview fails
    //         const fallbackPdf = new jsPDF('p', 'mm', 'a4');
    //         fallbackPdf.text(
    //             'PDF generation failed. Please try again.',
    //             20,
    //             20,
    //         );
    //         fallbackPdf.save(`${filename}_error.pdf`);
    //     } finally {
    //         removeSafeStyles();
    //         document.body.style.overflow = originalOverflow;
    //         content.style.cssText = originalStyle;
    //         isProcessing.value = false;
    //     }
    // };

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
    // const exportToPDF = async (elementId: string, filename = 'document') => {
    //     isProcessing.value = true;
    //     const element = document.getElementById(elementId);
    //     if (!element) return alert('Element not found: ' + elementId);
    //     // Temporary safe styles
    //     const safeStyle = document.createElement('style');
    //     safeStyle.id = 'pdf-safe';
    //     safeStyle.textContent = `
    //   * {
    //     color: #000 !important;
    //     background-color: #fff !important;
    //     border-color: #000 !important;
    //     box-shadow: none !important;
    //     line-height: 1.5 !important;
    //   }
    //   p, span, li, h1,h2,h3,h4,h5,h6 { margin-bottom: 0.5rem !important; }
    //   img { filter: none !important; opacity: 1 !important; }
    //   #${elementId} { max-width: 780px; padding: 10px; }
    //   .dark *, .dark { background-color: #fff !important; color: #000 !important; }
    // `;
    //     document.head.appendChild(safeStyle);
    //
    //     isProcessing.value = true;
    //
    //     try {
    //         const canvas = await html2canvas(element, {
    //             scale: 2,
    //             backgroundColor: '#ffffff',
    //             allowTaint: true,
    //         });
    //
    //         const imgData = canvas.toDataURL('image/png');
    //
    //         const pdf = new jsPDF({
    //             unit: 'mm',
    //             format: 'a4',
    //             orientation: 'portrait',
    //         });
    //         const pageWidth = pdf.internal.pageSize.getWidth();
    //         const pageHeight = pdf.internal.pageSize.getHeight();
    //         const margin = 10;
    //
    //         // Reserve space for header/footer
    //         const headerHeight = 15; // in mm
    //         const footerHeight = 10; // in mm
    //         const usablePageHeight =
    //             pageHeight - headerHeight - footerHeight - 2 * margin;
    //
    //         const imgProps = pdf.getImageProperties(imgData);
    //         const pdfWidth = pageWidth - 2 * margin;
    //         const pdfHeightFull = (imgProps.height * pdfWidth) / imgProps.width;
    //
    //         let position = 0;
    //         let page = 1;
    //
    //         while (position < pdfHeightFull) {
    //             const canvasPage = document.createElement('canvas');
    //             canvasPage.width = canvas.width;
    //             canvasPage.height =
    //                 (canvas.height * usablePageHeight) / pdfHeightFull;
    //
    //             const ctx = canvasPage.getContext('2d');
    //             if (!ctx) break;
    //
    //             ctx.drawImage(
    //                 canvas,
    //                 0,
    //                 (canvas.height * position) / pdfHeightFull,
    //                 canvas.width,
    //                 canvasPage.height,
    //                 0,
    //                 0,
    //                 canvas.width,
    //                 canvasPage.height,
    //             );
    //
    //             const pageData = canvasPage.toDataURL('image/png');
    //             pdf.addImage(
    //                 pageData,
    //                 'PNG',
    //                 margin,
    //                 margin + headerHeight,
    //                 pdfWidth,
    //                 usablePageHeight,
    //             );
    //
    //             // Header
    //             pdf.setFontSize(12);
    //             pdf.text(companyName, pageWidth / 2, margin + 7, {
    //                 align: 'center',
    //             });
    //
    //             // Footer
    //             pdf.setFontSize(10);
    //             pdf.text(
    //                 `Page ${page}`,
    //                 pageWidth / 2,
    //                 pageHeight - margin - 2,
    //                 { align: 'center' },
    //             );
    //
    //             position += usablePageHeight;
    //             if (position < pdfHeightFull) pdf.addPage();
    //             page++;
    //         }
    //
    //         const blob = pdf.output('blob');
    //         const url = URL.createObjectURL(blob);
    //         const newTab = window.open(
    //             url,
    //             '_blank',
    //             'width=1000,height=800,scrollbars=yes',
    //         );
    //         // if (!newTab) alert('Please allow popups to preview PDF');
    //     } catch (err) {
    //         console.error('PDF generation failed:', err);
    //     }
    //
    //     document.getElementById('pdf-safe')?.remove();
    //     isProcessing.value = false;
    // };

    // const exportToPDF = async (
    //     elementId: string,
    //     filename = 'document',
    //     orientationArg: 'portrait' | 'landscape' = 'portrait'
    // ) => {
    //     if (isProcessing.value) return;
    //
    //     // 1. Open window immediately
    //     const pdfWindow = window.open('', 'PDF_Preview', 'width=1100,height=850');
    //     if (!pdfWindow) return alert("Please allow popups");
    //     pdfWindow.document.write('<body style="display:flex;justify-content:center;align-items:center;height:100vh;font-family:sans-serif;"><h2>Generating PDF...</h2></body>');
    //
    //     isProcessing.value = true;
    //     const element = document.getElementById(elementId);
    //     if (!element) {
    //         pdfWindow.close();
    //         isProcessing.value = false;
    //         return;
    //     }
    //
    //     try {
    //         const canvas = await html2canvas(element, {
    //             scale: 2,
    //             useCORS: true,
    //             backgroundColor: '#ffffff',
    //             // THE CRITICAL FIX IS HERE
    //             onclone: (clonedDoc) => {
    //                 const style = clonedDoc.createElement('style');
    //                 // This CSS block forces the browser to resolve all colors to HEX/RGB
    //                 // and kills modern CSS variables before the library can parse them.
    //                 style.textContent = `
    //                 * {
    //                     color: #000000 !important;
    //                     background-color: #ffffff !important;
    //                     border-color: #000000 !important;
    //                     background-image: none !important;
    //                     box-shadow: none !important;
    //                     text-shadow: none !important;
    //                     /* Reset Tailwind/DaisyUI variables globally */
    //                     --p: 0 0% 0% !important;
    //                     --s: 0 0% 0% !important;
    //                     --a: 0 0% 0% !important;
    //                     --n: 0 0% 0% !important;
    //                     --bc: 255 255 255 !important;
    //                     --pc: 0 0% 0% !important;
    //                 }
    //             `;
    //                 clonedDoc.head.appendChild(style);
    //
    //                 // Force individual element styles to be "flat"
    //                 const all = clonedDoc.getElementById(elementId)?.getElementsByTagName('*');
    //                 if (all) {
    //                     for (let i = 0; i < all.length; i++) {
    //                         const el = all[i] as HTMLElement;
    //                         el.style.backgroundImage = 'none';
    //                         el.style.color = '#000000';
    //                     }
    //                 }
    //             }
    //         });
    //
    //         // 2. Setup PDF (A4)
    //         const pdf = new jsPDF({
    //             unit: 'mm',
    //             format: 'a4',
    //             orientation: orientationArg === 'landscape' ? 'l' : 'p',
    //         });
    //
    //         const pageWidth = pdf.internal.pageSize.getWidth();
    //         const pageHeight = pdf.internal.pageSize.getHeight();
    //         const margin = 10;
    //         const headerH = 15;
    //         const footerH = 15;
    //         const usableH = pageHeight - headerH - footerH - (margin * 2);
    //
    //         const imgData = canvas.toDataURL('image/png');
    //         const imgProps = pdf.getImageProperties(imgData);
    //         const pdfWidth = pageWidth - (margin * 2);
    //         const pdfHeightFull = (imgProps.height * pdfWidth) / imgProps.width;
    //
    //         let position = 0;
    //         let pageNum = 1;
    //
    //         // 3. Multi-page slicing logic
    //         while (position < pdfHeightFull) {
    //             const canvasPage = document.createElement('canvas');
    //             canvasPage.width = canvas.width;
    //             canvasPage.height = (canvas.height * usableH) / pdfHeightFull;
    //             const ctx = canvasPage.getContext('2d');
    //
    //             if (ctx) {
    //                 ctx.drawImage(canvas, 0, (canvas.height * position) / pdfHeightFull, canvas.width, canvasPage.height, 0, 0, canvas.width, canvasPage.height);
    //                 pdf.addImage(canvasPage.toDataURL('image/png'), 'PNG', margin, margin + headerH, pdfWidth, usableH);
    //             }
    //
    //             pdf.setFontSize(10);
    //             pdf.text(filename, pageWidth / 2, margin + 7, { align: 'center' });
    //             pdf.text(`Page ${pageNum}`, pageWidth / 2, pageHeight - margin, { align: 'center' });
    //
    //             position += usableH;
    //             if (position < pdfHeightFull) {
    //                 pdf.addPage();
    //                 pageNum++;
    //             }
    //         }
    //
    //         const blob = pdf.output('blob');
    //         pdfWindow.location.href = URL.createObjectURL(blob);
    //
    //     } catch (err: any) {
    //         console.error("PDF Export Error:", err);
    //         pdfWindow.document.body.innerHTML = `<h2 style="color:red; text-align:center;">Error: ${err.message}</h2>`;
    //     } finally {
    //         isProcessing.value = false;
    //     }
    // };


// Default values are set here: filename = 'document', orientation = 'portrait'


    const exportToPDF = async (
        elementId: string,
        filename = '',
        orientationArg: 'portrait' | 'landscape' = 'portrait',
        headerHtml='',
        isDownlaod=0,
    ) => {
        if (isProcessing.value) return;

        // 1. Open window immediately with Blinking Red Loading Text
        const pdfWindow = window.open('', 'PDF_Preview', 'width=1100,height=850');
        if (!pdfWindow) return alert("Please allow popups");

        pdfWindow.document.write(`
        <style>
            @keyframes blink {
                0% { opacity: 1; }
                50% { opacity: 0.3; }
                100% { opacity: 1; }
            }
            .blinking-text {
                color: #ff0000;
                font-family: sans-serif;
                font-weight: bold;
                font-size: 24px;
                animation: blink 1s linear infinite;
            }
            body {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                margin: 0;
                background-color: #f9f9f9;
            }
        </style>
        <div class="blinking-text">Generating PDF, please wait...</div>
    `);

        isProcessing.value = true;
        const element = document.getElementById(elementId);
        if (!element) {
            pdfWindow.close();
            isProcessing.value = false;
            return;
        }

        try {
            const canvas = await html2canvas(element, {
                scale: 2,
                useCORS: true,
                backgroundColor: '#ffffff',
                onclone: (clonedDoc) => {
                    const style = clonedDoc.createElement('style');
                    style.textContent = `
                    * {
                        color: #000000 !important;
                        background-color: #ffffff !important;
                        border-color: #000000 !important;
                        background-image: none !important;
                        box-shadow: none !important;
                        text-shadow: none !important;
                        --p: 0 0% 0% !important;
                        --s: 0 0% 0% !important;
                        --bc: 255 255 255 !important;
                    }
                `;
                    clonedDoc.head.appendChild(style);
                }
            });

            const pdfOrientation = orientationArg === 'landscape' ? 'l' : 'p';
            const pdf = new jsPDF({
                unit: 'mm',
                format: 'a4',
                orientation: pdfOrientation,
            });

            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();
            const margin = 10;

            // Remove top space (headerH) if filename is empty
            const headerH = headerHtml.trim() ? 15 : 0;
            const footerH = 15;
            const usableH = pageHeight - headerH - footerH - (margin * 2);

            const imgData = canvas.toDataURL('image/png');
            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pageWidth - (margin * 2);
            const pdfHeightFull = (imgProps.height * pdfWidth) / imgProps.width;

            let position = 0;
            let pageNum = 1;

            while (position < pdfHeightFull) {
                const canvasPage = document.createElement('canvas');
                canvasPage.width = canvas.width;
                canvasPage.height = (canvas.height * usableH) / pdfHeightFull;
                const ctx = canvasPage.getContext('2d');

                if (ctx) {
                    ctx.drawImage(canvas, 0, (canvas.height * position) / pdfHeightFull, canvas.width, canvasPage.height, 0, 0, canvas.width, canvasPage.height);
                    pdf.addImage(canvasPage.toDataURL('image/png'), 'PNG', margin, margin + headerH, pdfWidth, usableH);
                }

                if (headerHtml.trim()) {
                    pdf.setFontSize(10);
                    pdf.text(headerHtml, pageWidth / 2, margin + 7, { align: 'center' });
                }

                pdf.setFontSize(10);
                pdf.text(`Page ${pageNum}`, pageWidth / 2, pageHeight - margin, { align: 'center' });

                position += usableH;
                if (position < pdfHeightFull) {
                    pdf.addPage();
                    pageNum++;
                }
            }

            const blob = pdf.output('blob');
            if(isDownlaod){
                const url = URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;                         // MUST be blob URL
                a.download = filename+".pdf";  // THIS controls filename
                a.click();
            }else{
                pdfWindow.location.href = URL.createObjectURL(blob);
            }


        } catch (err: any) {
            console.error("PDF Export Error:", err);
            pdfWindow.document.body.innerHTML = `<h2 style="color:red; text-align:center;">Error: ${err.message}</h2>`;
        } finally {
            isProcessing.value = false;
        }
    };
    return {
        isProcessing,
        print,
        exportToExcel,
        exportToPDF,
    };
}
