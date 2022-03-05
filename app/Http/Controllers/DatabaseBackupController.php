<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use PDO;


class DatabaseBackupController extends Controller
{
    public function backup()
    {
        $tables = array(
            "websockets_statistics_entries",
            "roles",
            "users",
            "password_resets",
            "failed_jobs",
            "personal_access_tokens",
            "suppliers",
            "customers",
            "departments",
            "schedules",
            "branches",
            "deductions",
            "positions",
            "employees",
            "assets",
            "ingredients",
            "asset_ingredients",
            "customize_cake_projects",
            "categories",
            "project_assets",
            "products",
            "careers",
            "applicants",
            "stocks",
            "orders",
            "carts",
            "channels",
            "subscribers",
            "messages",
            "expenses",
            "attendances",
            "payrolls",
            "stock_transfers",
            "overtimes",
            "carriers",
            "parcels"
        );

        $connect  = DB::connection()->getPdo();

        $output = '';

        foreach ($tables as $table) {
            $stmt = $connect->prepare("SHOW CREATE TABLE $table");
            $stmt->execute();
            $results = $stmt->fetchAll();

            foreach ($results as $row) {
                $output .= "\n\n" . $row['Create Table'] . ";\n\n";
            }

            $stmt = $connect->prepare("SELECT * FROM $table");
            $stmt->execute();
            $rowCount = $stmt->rowCount();

            for ($i = 0; $i < $rowCount; $i++) {
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $columns = array_keys($result);
                $values = array_values($result);
                foreach ($values as $key => $value) {
                    if (is_null($value)) {
                        $values[$key] = "null";
                    }

                    if (gettype($value) === "string") {
                        $values[$key] = "'$value'";
                    }

                }

                $output .= "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");\n";
            }
        }

        $filename = date('Y-m-d') . ' database_backup.sql';
        $fileHandle = fopen($filename, 'w+');
        fwrite($fileHandle, $output);
        fclose($fileHandle);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($filename));
        header('Access-Control-Allow-Origin: *');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filename));
        ob_clean();
        flush();
        readfile($filename);
        unlink($filename);
    }
}
