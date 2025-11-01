<?php
/**
 * PDF Helper Class
 *
 * DomPDF wrapper for generating PDF documents
 *
 * @package App\Helpers
 * @author Optix Development Team
 * @version 1.0
 */

namespace App\Helpers;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDF
{
    /**
     * @var Dompdf DomPDF instance
     */
    private Dompdf $dompdf;

    /**
     * @var Options PDF options
     */
    private Options $options;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->options = new Options();
        $this->options->set('defaultFont', 'Arial');
        $this->options->set('isRemoteEnabled', true);
        $this->options->set('isHtml5ParserEnabled', true);

        $this->dompdf = new Dompdf($this->options);
    }

    /**
     * Generate PDF from HTML
     *
     * @param string $html HTML content
     * @param string $filename Output filename
     * @param string $orientation Page orientation (portrait/landscape)
     * @param string $paperSize Paper size (letter, a4, etc.)
     * @return string PDF content
     */
    public function generate(string $html, string $filename = 'document.pdf', string $orientation = 'portrait', string $paperSize = 'letter'): string
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paperSize, $orientation);
        $this->dompdf->render();

        return $this->dompdf->output();
    }

    /**
     * Generate and save PDF to file
     *
     * @param string $html HTML content
     * @param string $filepath Full file path
     * @param string $orientation Page orientation
     * @param string $paperSize Paper size
     * @return bool
     */
    public function save(string $html, string $filepath, string $orientation = 'portrait', string $paperSize = 'letter'): bool
    {
        $output = $this->generate($html, basename($filepath), $orientation, $paperSize);
        return file_put_contents($filepath, $output) !== false;
    }

    /**
     * Generate and stream PDF to browser
     *
     * @param string $html HTML content
     * @param string $filename Output filename
     * @param string $orientation Page orientation
     * @param string $paperSize Paper size
     * @return void
     */
    public function stream(string $html, string $filename = 'document.pdf', string $orientation = 'portrait', string $paperSize = 'letter'): void
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paperSize, $orientation);
        $this->dompdf->render();
        $this->dompdf->stream($filename, ['Attachment' => false]);
    }

    /**
     * Generate and download PDF
     *
     * @param string $html HTML content
     * @param string $filename Output filename
     * @param string $orientation Page orientation
     * @param string $paperSize Paper size
     * @return void
     */
    public function download(string $html, string $filename = 'document.pdf', string $orientation = 'portrait', string $paperSize = 'letter'): void
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paperSize, $orientation);
        $this->dompdf->render();
        $this->dompdf->stream($filename, ['Attachment' => true]);
    }

    /**
     * Generate receipt PDF
     *
     * @param array $transaction Transaction data
     * @param array $items Transaction items
     * @return string PDF content
     */
    public function generateReceipt(array $transaction, array $items): string
    {
        $html = $this->getReceiptTemplate($transaction, $items);
        return $this->generate($html, 'receipt_' . $transaction['id'] . '.pdf');
    }

    /**
     * Generate prescription PDF
     *
     * @param array $prescription Prescription data
     * @param array $patient Patient data
     * @return string PDF content
     */
    public function generatePrescription(array $prescription, array $patient): string
    {
        $html = $this->getPrescriptionTemplate($prescription, $patient);
        return $this->generate($html, 'prescription_' . $prescription['id'] . '.pdf');
    }

    /**
     * Generate examination report PDF
     *
     * @param array $examination Examination data
     * @param array $patient Patient data
     * @return string PDF content
     */
    public function generateExaminationReport(array $examination, array $patient): string
    {
        $html = $this->getExaminationTemplate($examination, $patient);
        return $this->generate($html, 'examination_' . $examination['id'] . '.pdf');
    }

    /**
     * Get receipt HTML template
     *
     * @param array $transaction Transaction data
     * @param array $items Transaction items
     * @return string
     */
    private function getReceiptTemplate(array $transaction, array $items): string
    {
        $itemsHtml = '';
        foreach ($items as $item) {
            $itemTotal = $item['quantity'] * $item['price'];
            $itemsHtml .= "
                <tr>
                    <td>{$item['product_name']}</td>
                    <td style='text-align: center;'>{$item['quantity']}</td>
                    <td style='text-align: right;'>$" . number_format($item['price'], 2) . "</td>
                    <td style='text-align: right;'>$" . number_format($itemTotal, 2) . "</td>
                </tr>
            ";
        }

        $subtotal = $transaction['subtotal'] ?? 0;
        $tax = $transaction['tax'] ?? 0;
        $discount = $transaction['discount'] ?? 0;
        $total = $transaction['total'] ?? 0;

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; }
                .header { text-align: center; margin-bottom: 30px; }
                .company-name { font-size: 24px; font-weight: bold; margin-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { padding: 10px; border-bottom: 1px solid #ddd; }
                th { background-color: #f4f4f4; text-align: left; }
                .totals { margin-top: 20px; text-align: right; }
                .totals table { width: 300px; margin-left: auto; }
                .total-row { font-weight: bold; font-size: 14px; }
                .footer { margin-top: 40px; text-align: center; font-size: 10px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='company-name'>" . APP_NAME . "</div>
                <div>Receipt #" . $transaction['id'] . "</div>
                <div>" . date('F j, Y g:i A', strtotime($transaction['created_at'])) . "</div>
            </div>

            <div>
                <strong>Customer:</strong> {$transaction['patient_name']}<br>
                <strong>Cashier:</strong> {$transaction['cashier_name']}
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Item</th>
                        <th style='text-align: center;'>Qty</th>
                        <th style='text-align: right;'>Price</th>
                        <th style='text-align: right;'>Total</th>
                    </tr>
                </thead>
                <tbody>
                    {$itemsHtml}
                </tbody>
            </table>

            <div class='totals'>
                <table>
                    <tr>
                        <td>Subtotal:</td>
                        <td style='text-align: right;'>$" . number_format($subtotal, 2) . "</td>
                    </tr>
                    <tr>
                        <td>Tax:</td>
                        <td style='text-align: right;'>$" . number_format($tax, 2) . "</td>
                    </tr>
                    <tr>
                        <td>Discount:</td>
                        <td style='text-align: right;'>-$" . number_format($discount, 2) . "</td>
                    </tr>
                    <tr class='total-row'>
                        <td>Total:</td>
                        <td style='text-align: right;'>$" . number_format($total, 2) . "</td>
                    </tr>
                </table>
            </div>

            <div class='footer'>
                <p>Thank you for your business!</p>
                <p>Please retain this receipt for your records.</p>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get prescription HTML template
     *
     * @param array $prescription Prescription data
     * @param array $patient Patient data
     * @return string
     */
    private function getPrescriptionTemplate(array $prescription, array $patient): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; font-size: 12px; padding: 20px; }
                .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                .company-name { font-size: 20px; font-weight: bold; }
                .section { margin: 20px 0; }
                .section-title { font-size: 14px; font-weight: bold; background-color: #f4f4f4; padding: 5px; margin-bottom: 10px; }
                table { width: 100%; border-collapse: collapse; }
                td { padding: 8px; }
                .prescription-box { border: 2px solid #333; padding: 20px; margin: 20px 0; }
                .footer { margin-top: 50px; border-top: 1px solid #333; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class='header'>
                <div class='company-name'>" . APP_NAME . "</div>
                <div>Prescription</div>
            </div>

            <div class='section'>
                <div class='section-title'>Patient Information</div>
                <table>
                    <tr>
                        <td><strong>Name:</strong> {$patient['first_name']} {$patient['last_name']}</td>
                        <td><strong>DOB:</strong> " . date('m/d/Y', strtotime($patient['date_of_birth'])) . "</td>
                    </tr>
                    <tr>
                        <td><strong>Date:</strong> " . date('m/d/Y', strtotime($prescription['created_at'])) . "</td>
                        <td><strong>Prescription #:</strong> {$prescription['id']}</td>
                    </tr>
                </table>
            </div>

            <div class='prescription-box'>
                <div class='section-title'>Prescription Details</div>
                <table>
                    <tr>
                        <th></th>
                        <th>SPH</th>
                        <th>CYL</th>
                        <th>AXIS</th>
                        <th>ADD</th>
                        <th>PD</th>
                    </tr>
                    <tr>
                        <td><strong>OD (Right)</strong></td>
                        <td>{$prescription['od_sphere']}</td>
                        <td>{$prescription['od_cylinder']}</td>
                        <td>{$prescription['od_axis']}</td>
                        <td>{$prescription['od_add']}</td>
                        <td>{$prescription['od_pd']}</td>
                    </tr>
                    <tr>
                        <td><strong>OS (Left)</strong></td>
                        <td>{$prescription['os_sphere']}</td>
                        <td>{$prescription['os_cylinder']}</td>
                        <td>{$prescription['os_axis']}</td>
                        <td>{$prescription['os_add']}</td>
                        <td>{$prescription['os_pd']}</td>
                    </tr>
                </table>
            </div>

            <div class='footer'>
                <table>
                    <tr>
                        <td><strong>Provider:</strong> {$prescription['provider_name']}</td>
                    </tr>
                    <tr>
                        <td><strong>Signature:</strong> ___________________________</td>
                    </tr>
                </table>
            </div>
        </body>
        </html>
        ";
    }

    /**
     * Get examination HTML template
     *
     * @param array $examination Examination data
     * @param array $patient Patient data
     * @return string
     */
    private function getExaminationTemplate(array $examination, array $patient): string
    {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; font-size: 11px; padding: 20px; }
                .header { text-align: center; margin-bottom: 20px; }
                .section { margin: 15px 0; }
                .section-title { font-weight: bold; background-color: #f4f4f4; padding: 5px; }
                table { width: 100%; border-collapse: collapse; margin: 10px 0; }
                td { padding: 5px; border: 1px solid #ddd; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h2>" . APP_NAME . "</h2>
                <h3>Examination Report</h3>
            </div>

            <div class='section'>
                <div class='section-title'>Patient Information</div>
                <p><strong>Name:</strong> {$patient['first_name']} {$patient['last_name']}<br>
                <strong>DOB:</strong> " . date('m/d/Y', strtotime($patient['date_of_birth'])) . "<br>
                <strong>Exam Date:</strong> " . date('m/d/Y', strtotime($examination['exam_date'])) . "</p>
            </div>

            <div class='section'>
                <div class='section-title'>Chief Complaint</div>
                <p>{$examination['chief_complaint']}</p>
            </div>

            <div class='section'>
                <div class='section-title'>Visual Acuity</div>
                <table>
                    <tr>
                        <td><strong>OD:</strong> {$examination['va_od']}</td>
                        <td><strong>OS:</strong> {$examination['va_os']}</td>
                    </tr>
                </table>
            </div>

            <div class='section'>
                <div class='section-title'>Assessment & Plan</div>
                <p>{$examination['assessment']}</p>
            </div>

            <div class='section'>
                <div class='section-title'>Provider</div>
                <p>{$examination['provider_name']}</p>
            </div>
        </body>
        </html>
        ";
    }
}
