<?php

/**
 * @author   Ruban Edward
 * @category Helper
 * @purpose  Helper Function to downlaod the sample files based on the file they want
 */

if (!function_exists('downloadSampleFile')) {
    /**
     * Downloads a sample file.
     *
     * @param string $filePath Path to the file to be downloaded.
     */
    function downloadSampleFile(string $fileName)
    {
        // Get the instance of the CodeIgniter application
        $app = \Config\Services::response();

        // Resolve inside the support-files directory only (blocks ../ traversal)
        $baseDir        = realpath(FCPATH . 'support/supportFiles');
        $reference_file = $baseDir !== false ? realpath($baseDir . DIRECTORY_SEPARATOR . basename($fileName)) : false;

        if ($reference_file !== false && is_file($reference_file)) {
            // Set headers for downloading the file
            $app->setHeader('Content-Description', 'File Transfer');
            $app->setHeader('Content-Type', 'application/octet-stream');
            $app->setHeader('Content-Disposition', 'attachment; filename="' . basename($reference_file) . '"');
            $app->setHeader('Expires', '0');
            $app->setHeader('Cache-Control', 'must-revalidate');
            $app->setHeader('Pragma', 'public');
            $app->setHeader('Content-Length', filesize($reference_file));

            // Return the response with the file content
            return $app->setBody(file_get_contents($reference_file));
        } else {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Reference file not found');
        }
    }
}