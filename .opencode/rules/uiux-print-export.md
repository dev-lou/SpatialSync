---
description: Deep dive on print and PDF export. Covers @media print, window.print(), jsPDF integration, page breaks, and repeating headers.
---

# UI/UX Print & PDF Export

> Complete guide for print-optimized pages and PDF generation.
> Token-aware styling that respects design language.

---

## OVERVIEW

This skill covers:
1. `@media print` CSS for browser print
2. Print button components
3. jsPDF + html2canvas for programmatic PDF
4. Page breaks and repeating headers
5. Print-specific accessibility

---

## @MEDIA PRINT FUNDAMENTALS

### Base Print Styles

```css
@media print {
  /* Hide non-essential elements */
  .no-print,
  .sidebar,
  .navigation,
  .footer,
  .toast,
  .modal,
  [role="navigation"],
  [role="banner"],
  button:not(.print-include) {
    display: none !important;
  }
  
  /* Reset backgrounds for ink saving */
  body {
    background: white !important;
    color: black !important;
    font-size: 12pt;
    line-height: 1.5;
  }
  
  /* Expand links with URLs */
  a[href]::after {
    content: " (" attr(href) ")";
    font-size: 0.8em;
    color: #666;
  }
  
  /* Don't show URL for internal/JS links */
  a[href^="#"]::after,
  a[href^="javascript:"]::after {
    content: none;
  }
  
  /* Page break utilities */
  .page-break {
    page-break-before: always;
  }
  
  .no-break {
    page-break-inside: avoid;
  }
  
  .keep-together {
    page-break-inside: avoid;
    break-inside: avoid;
  }
  
  /* Ensure images don't overflow */
  img {
    max-width: 100% !important;
    page-break-inside: avoid;
  }
  
  /* Tables */
  table {
    border-collapse: collapse;
  }
  
  th, td {
    border: 1px solid #000;
    padding: 8px;
  }
  
  thead {
    display: table-header-group; /* Repeat on each page */
  }
  
  tr {
    page-break-inside: avoid;
  }
}
```

### Design Language Print Variations

```css
/* Luxury - Elegant serif print */
[data-theme="light"][data-language="luxury"] {
  @media print {
    body {
      font-family: 'Playfair Display', Georgia, serif;
      font-size: 11pt;
      letter-spacing: 0.02em;
    }
    
    h1, h2, h3 {
      font-weight: 400;
      letter-spacing: 0.05em;
      text-transform: uppercase;
    }
  }
}

/* Technical - Monospace print */
[data-language="technical"] {
  @media print {
    body {
      font-family: 'JetBrains Mono', monospace;
      font-size: 10pt;
    }
    
    code, pre {
      background: #f5f5f5 !important;
      border: 1px solid #ddd;
    }
  }
}
```

---

## PRINT BUTTON COMPONENT

### HTML Structure

```html
<button class="btn btn--print" onclick="window.print()">
  <i data-lucide="printer"></i>
  <span>Print</span>
</button>
```

### Token-Aware Styles

```css
.btn--print {
  display: inline-flex;
  align-items: center;
  gap: var(--space-2);
  padding: var(--space-2) var(--space-4);
  background: var(--surface);
  color: var(--text);
  border: 1px solid var(--border);
  border-radius: var(--radius-md);
  font-size: var(--text-sm);
  cursor: pointer;
  transition: background var(--transition-base);
}

.btn--print:hover {
  background: var(--bg-hover);
}

.btn--print:focus-visible {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
}

/* Hide print button when printing */
@media print {
  .btn--print {
    display: none !important;
  }
}
```

### React Component

```tsx
import { Printer } from 'lucide-react';

interface PrintButtonProps {
  label?: string;
  className?: string;
}

export function PrintButton({ label = 'Print', className = '' }: PrintButtonProps) {
  return (
    <button 
      className={`btn btn--print ${className}`}
      onClick={() => window.print()}
      aria-label="Print this page"
    >
      <Printer size={16} aria-hidden="true" />
      <span>{label}</span>
    </button>
  );
}
```

---

## JSPDF INTEGRATION

### Installation

```bash
npm install jspdf html2canvas
```

### Basic PDF Export

```typescript
import jsPDF from 'jspdf';
import html2canvas from 'html2canvas';

interface PDFExportOptions {
  filename?: string;
  format?: 'a4' | 'letter';
  orientation?: 'portrait' | 'landscape';
  margin?: number;
  quality?: number;
}

export async function exportToPDF(
  elementId: string,
  options: PDFExportOptions = {}
): Promise<void> {
  const {
    filename = 'document.pdf',
    format = 'a4',
    orientation = 'portrait',
    margin = 10,
    quality = 2
  } = options;
  
  const element = document.getElementById(elementId);
  if (!element) {
    throw new Error(`Element #${elementId} not found`);
  }
  
  // Show loading state
  const loadingToast = showToast('Generating PDF...', 'loading');
  
  try {
    // Capture element as canvas
    const canvas = await html2canvas(element, {
      scale: quality,
      useCORS: true,
      logging: false,
      backgroundColor: '#ffffff'
    });
    
    // Create PDF
    const pdf = new jsPDF({
      orientation,
      unit: 'mm',
      format
    });
    
    const pageWidth = pdf.internal.pageSize.getWidth();
    const pageHeight = pdf.internal.pageSize.getHeight();
    const imgWidth = pageWidth - (margin * 2);
    const imgHeight = (canvas.height * imgWidth) / canvas.width;
    
    // Add image to PDF (handles multi-page)
    let heightLeft = imgHeight;
    let position = margin;
    
    pdf.addImage(
      canvas.toDataURL('image/png'),
      'PNG',
      margin,
      position,
      imgWidth,
      imgHeight
    );
    
    heightLeft -= (pageHeight - margin * 2);
    
    // Add additional pages if needed
    while (heightLeft > 0) {
      position = heightLeft - imgHeight + margin;
      pdf.addPage();
      pdf.addImage(
        canvas.toDataURL('image/png'),
        'PNG',
        margin,
        position,
        imgWidth,
        imgHeight
      );
      heightLeft -= (pageHeight - margin * 2);
    }
    
    // Download
    pdf.save(filename);
    
    hideToast(loadingToast);
    showToast('PDF downloaded successfully', 'success');
    
  } catch (error) {
    hideToast(loadingToast);
    showToast('Failed to generate PDF', 'error');
    console.error('PDF export error:', error);
  }
}
```

### Export Button Component

```tsx
import { FileDown } from 'lucide-react';
import { exportToPDF } from '@/utils/pdf-export';
import { useState } from 'react';

interface ExportPDFButtonProps {
  targetId: string;
  filename?: string;
  label?: string;
}

export function ExportPDFButton({ 
  targetId, 
  filename = 'document.pdf',
  label = 'Export PDF' 
}: ExportPDFButtonProps) {
  const [isExporting, setIsExporting] = useState(false);
  
  const handleExport = async () => {
    setIsExporting(true);
    try {
      await exportToPDF(targetId, { filename });
    } finally {
      setIsExporting(false);
    }
  };
  
  return (
    <button 
      className="btn btn--secondary"
      onClick={handleExport}
      disabled={isExporting}
      aria-busy={isExporting}
    >
      <FileDown size={16} aria-hidden="true" />
      <span>{isExporting ? 'Generating...' : label}</span>
    </button>
  );
}
```

---

## PAGE BREAKS

### CSS Classes

```css
/* Force page break before element */
.page-break-before {
  page-break-before: always;
  break-before: page;
}

/* Force page break after element */
.page-break-after {
  page-break-after: always;
  break-after: page;
}

/* Prevent page break inside element */
.no-break-inside {
  page-break-inside: avoid;
  break-inside: avoid;
}

/* Ensure element starts on new page with heading */
.chapter {
  page-break-before: always;
  break-before: page;
}

/* Keep heading with following content */
h1, h2, h3, h4, h5, h6 {
  page-break-after: avoid;
  break-after: avoid;
}

/* Orphan/widow control */
p {
  orphans: 3;
  widows: 3;
}
```

### Invoice Example with Page Breaks

```html
<div class="invoice" id="invoice-content">
  <!-- Header - appears on first page only -->
  <header class="invoice__header no-break-inside">
    <img src="/logo.svg" alt="Company Logo" class="invoice__logo">
    <div class="invoice__details">
      <h1>Invoice #12345</h1>
      <p>Date: January 15, 2026</p>
    </div>
  </header>
  
  <!-- Line items - kept together -->
  <table class="invoice__items">
    <thead>
      <tr>
        <th>Description</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <!-- rows -->
    </tbody>
  </table>
  
  <!-- Terms - new page if needed -->
  <section class="invoice__terms page-break-before">
    <h2>Terms & Conditions</h2>
    <!-- content -->
  </section>
</div>
```

---

## REPEATING HEADERS (TABLES)

### CSS Approach

```css
@media print {
  table {
    width: 100%;
    border-collapse: collapse;
  }
  
  thead {
    display: table-header-group; /* Repeat header on each page */
  }
  
  tfoot {
    display: table-footer-group; /* Repeat footer on each page */
  }
  
  tbody {
    display: table-row-group;
  }
  
  tr {
    page-break-inside: avoid;
  }
}
```

### jsPDF Table with Headers

```typescript
import jsPDF from 'jspdf';
import 'jspdf-autotable';

interface TableData {
  headers: string[];
  rows: (string | number)[][];
}

export function exportTableToPDF(data: TableData, filename: string) {
  const doc = new jsPDF();
  
  // Add title
  doc.setFontSize(18);
  doc.text('Report', 14, 22);
  doc.setFontSize(10);
  doc.text(`Generated: ${new Date().toLocaleDateString()}`, 14, 28);
  
  // Add table with repeating headers
  (doc as any).autoTable({
    head: [data.headers],
    body: data.rows,
    startY: 35,
    
    // Style with design tokens (mapped to PDF values)
    headStyles: {
      fillColor: [77, 142, 255], // --accent
      textColor: [255, 255, 255],
      fontStyle: 'bold',
      halign: 'left',
      cellPadding: 4
    },
    
    bodyStyles: {
      cellPadding: 4,
      fontSize: 10,
      valign: 'middle'
    },
    
    alternateRowStyles: {
      fillColor: [248, 249, 250] // --surface
    },
    
    // Repeat header on each page
    showHead: 'everyPage',
    
    // Keep rows together
    rowPageBreak: 'avoid',
    
    // Add page numbers
    didDrawPage: (data: any) => {
      const pageCount = doc.internal.getNumberOfPages();
      const currentPage = data.pageNumber;
      doc.setFontSize(8);
      doc.text(
        `Page ${currentPage} of ${pageCount}`,
        doc.internal.pageSize.getWidth() / 2,
        doc.internal.pageSize.getHeight() - 10,
        { align: 'center' }
      );
    }
  });
  
  doc.save(filename);
}
```

---

## PRINT-SPECIFIC CONTENT

### Show/Hide for Print

```css
/* Hide on screen, show in print */
.print-only {
  display: none;
}

@media print {
  .print-only {
    display: block !important;
  }
  
  .no-print {
    display: none !important;
  }
}
```

### Print Header/Footer

```html
<!-- Only visible when printing -->
<header class="print-only print-header">
  <img src="/logo-print.svg" alt="Company">
  <span class="print-header__title">Confidential Document</span>
</header>

<footer class="print-only print-footer">
  <span>© 2026 Company Name</span>
  <span class="print-footer__page">Page <span class="page-number"></span></span>
</footer>
```

```css
@media print {
  .print-header,
  .print-footer {
    position: fixed;
    left: 0;
    right: 0;
    padding: 10px 20px;
    font-size: 10pt;
    color: #666;
  }
  
  .print-header {
    top: 0;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  
  .print-footer {
    bottom: 0;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
  }
  
  /* Add spacing for header/footer */
  @page {
    margin: 2cm 1.5cm;
  }
}
```

---

## ACCESSIBILITY FOR PRINT

1. **Ensure sufficient contrast** - Black text on white background
2. **Expand abbreviations** - `<abbr title="...">` should show full text
3. **Show link URLs** - Users can't click printed links
4. **Include alt text** - Consider showing image descriptions
5. **Respect user preferences** - Don't force print colors

```css
@media print {
  /* Show abbreviation expansions */
  abbr[title]::after {
    content: " (" attr(title) ")";
  }
  
  /* Ensure images have visible alt text if image fails */
  img {
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  
  /* High contrast for readability */
  * {
    color: #000 !important;
    background: transparent !important;
    box-shadow: none !important;
    text-shadow: none !important;
  }
}
```

---

## PRINT PREVIEW BUTTON

```tsx
export function PrintPreviewButton() {
  const handlePreview = () => {
    // Open print dialog which shows preview
    window.print();
  };
  
  return (
    <button className="btn btn--ghost" onClick={handlePreview}>
      <Eye size={16} />
      <span>Print Preview</span>
    </button>
  );
}
```

---

## CHECKLIST

- [ ] `@media print` styles defined
- [ ] Non-essential elements hidden (`.no-print`)
- [ ] Links show URLs in print
- [ ] Page breaks configured
- [ ] Tables have repeating headers
- [ ] Images scaled appropriately
- [ ] Print button has correct styling
- [ ] PDF export tested with jsPDF
- [ ] Accessibility considerations addressed
- [ ] Design language preserved where appropriate
