import { extractPdfDocument } from './parser.js';

export async function parsePdfFile(file, onProgress) {
    if (typeof pdfjsLib === 'undefined') {
        throw new Error('PDF library is not available.');
    }

    onProgress?.('Extracting PDF...');
    const arrayBuffer = await file.arrayBuffer();
    const bytes = new Uint8Array(arrayBuffer);
    let binary = '';
    bytes.forEach((byte) => {
        binary += String.fromCharCode(byte);
    });

    const pdf = await pdfjsLib.getDocument({ data: arrayBuffer }).promise;
    onProgress?.('Analyzing programs...');
    const extracted = await extractPdfDocument(pdf);
    onProgress?.('Extracting activities...');

    return {
        pdfData: btoa(binary),
        ...extracted,
    };
}

export function buildSavePayload(payload, title, calendarYear) {
    const rows = payload.rows || [];
    const reviewCount = rows.filter((row) => row.manual_review_required).length;

    return {
        title,
        source_type: 'pdf',
        calendar_year: calendarYear,
        document_html: null,
        pdf_data: payload.pdfData,
        extracted_text: payload.extractedText,
        document: payload.document || {},
        rows,
        confirm_budget_warnings: reviewCount > 0,
    };
}
