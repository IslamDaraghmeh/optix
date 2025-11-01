<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add indexes to frequently queried columns for better performance
     *
     * @return void
     */
    public function up()
    {
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();

        // Helper function to check if index exists
        $indexExists = function($table, $indexName) use ($connection, $dbName) {
            $query = "SELECT COUNT(*) as count FROM information_schema.statistics
                      WHERE table_schema = ? AND table_name = ? AND index_name = ?";
            $result = $connection->selectOne($query, [$dbName, $table, $indexName]);
            return $result->count > 0;
        };

        // Patients table indexes
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('patients', 'patients_phone_index')) {
                    $table->index('phone', 'patients_phone_index');
                }
                if (!$indexExists('patients', 'patients_email_index')) {
                    $table->index('email', 'patients_email_index');
                }
            });
        }

        // Exams table indexes
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('exams', 'exams_exam_date_index')) {
                    $table->index('exam_date', 'exams_exam_date_index');
                }
                if (!$indexExists('exams', 'exams_patient_date_index')) {
                    $table->index(['patient_id', 'exam_date'], 'exams_patient_date_index');
                }
            });
        }

        // Glasses table indexes
        if (Schema::hasTable('glasses')) {
            Schema::table('glasses', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('glasses', 'glasses_status_index')) {
                    $table->index('status', 'glasses_status_index');
                }
                if (!$indexExists('glasses', 'glasses_status_created_index')) {
                    $table->index(['status', 'created_at'], 'glasses_status_created_index');
                }
            });
        }

        // Sales table indexes
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('sales', 'sales_sale_date_index')) {
                    $table->index('sale_date', 'sales_sale_date_index');
                }
                if (!$indexExists('sales', 'sales_remaining_amount_index')) {
                    $table->index('remaining_amount', 'sales_remaining_amount_index');
                }
                if (!$indexExists('sales', 'sales_patient_date_index')) {
                    $table->index(['patient_id', 'sale_date'], 'sales_patient_date_index');
                }
                if (!$indexExists('sales', 'sales_outstanding_index')) {
                    $table->index(['remaining_amount', 'sale_date'], 'sales_outstanding_index');
                }
            });
        }

        // Expenses table indexes
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('expenses', 'expenses_expense_date_index')) {
                    $table->index('expense_date', 'expenses_expense_date_index');
                }
                if (!$indexExists('expenses', 'expenses_category_index')) {
                    $table->index('category', 'expenses_category_index');
                }
                if (!$indexExists('expenses', 'expenses_date_category_index')) {
                    $table->index(['expense_date', 'category'], 'expenses_date_category_index');
                }
            });
        }

        // Glasses stock table indexes
        if (Schema::hasTable('glasses_stock')) {
            Schema::table('glasses_stock', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('glasses_stock', 'glasses_stock_item_type_index')) {
                    $table->index('item_type', 'glasses_stock_item_type_index');
                }
                if (!$indexExists('glasses_stock', 'glasses_stock_quantity_index')) {
                    $table->index('quantity', 'glasses_stock_quantity_index');
                }
                if (!$indexExists('glasses_stock', 'glasses_stock_type_quantity_index')) {
                    $table->index(['item_type', 'quantity'], 'glasses_stock_type_quantity_index');
                }
            });
        }

        // Stock movements table indexes
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) use ($indexExists) {
                if (!$indexExists('stock_movements', 'stock_movements_movement_type_index')) {
                    $table->index('movement_type', 'stock_movements_movement_type_index');
                }
                if (!$indexExists('stock_movements', 'stock_movements_stock_date_index')) {
                    $table->index(['stock_id', 'created_at'], 'stock_movements_stock_date_index');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = Schema::getConnection();
        $dbName = $connection->getDatabaseName();

        // Helper function to check if index exists
        $indexExists = function($table, $indexName) use ($connection, $dbName) {
            $query = "SELECT COUNT(*) as count FROM information_schema.statistics
                      WHERE table_schema = ? AND table_name = ? AND index_name = ?";
            $result = $connection->selectOne($query, [$dbName, $table, $indexName]);
            return $result->count > 0;
        };

        // Drop patients indexes
        if (Schema::hasTable('patients')) {
            Schema::table('patients', function (Blueprint $table) use ($indexExists) {
                if ($indexExists('patients', 'patients_phone_index')) {
                    $table->dropIndex('patients_phone_index');
                }
                if ($indexExists('patients', 'patients_email_index')) {
                    $table->dropIndex('patients_email_index');
                }
            });
        }

        // Drop exams indexes
        if (Schema::hasTable('exams')) {
            Schema::table('exams', function (Blueprint $table) use ($indexExists) {
                if ($indexExists('exams', 'exams_exam_date_index')) {
                    $table->dropIndex('exams_exam_date_index');
                }
                if ($indexExists('exams', 'exams_patient_date_index')) {
                    $table->dropIndex('exams_patient_date_index');
                }
            });
        }

        // Drop glasses indexes
        if (Schema::hasTable('glasses')) {
            Schema::table('glasses', function (Blueprint $table) use ($indexExists) {
                if ($indexExists('glasses', 'glasses_status_index')) {
                    $table->dropIndex('glasses_status_index');
                }
                if ($indexExists('glasses', 'glasses_status_created_index')) {
                    $table->dropIndex('glasses_status_created_index');
                }
            });
        }

        // Drop sales indexes
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $table) use ($indexExists) {
                if ($indexExists('sales', 'sales_sale_date_index')) {
                    $table->dropIndex('sales_sale_date_index');
                }
                if ($indexExists('sales', 'sales_remaining_amount_index')) {
                    $table->dropIndex('sales_remaining_amount_index');
                }
                if ($indexExists('sales', 'sales_patient_date_index')) {
                    $table->dropIndex('sales_patient_date_index');
                }
                if ($indexExists('sales', 'sales_outstanding_index')) {
                    $table->dropIndex('sales_outstanding_index');
                }
            });
        }

        // Drop expenses indexes
        if (Schema::hasTable('expenses')) {
            Schema::table('expenses', function (Blueprint $table) use ($indexExists) {
                if ($indexExists('expenses', 'expenses_expense_date_index')) {
                    $table->dropIndex('expenses_expense_date_index');
                }
                if ($indexExists('expenses', 'expenses_category_index')) {
                    $table->dropIndex('expenses_category_index');
                }
                if ($indexExists('expenses', 'expenses_date_category_index')) {
                    $table->dropIndex('expenses_date_category_index');
                }
            });
        }

        // Drop glasses stock indexes
        if (Schema::hasTable('glasses_stock')) {
            Schema::table('glasses_stock', function (Blueprint $table) use ($indexExists) {
                if ($indexExists('glasses_stock', 'glasses_stock_item_type_index')) {
                    $table->dropIndex('glasses_stock_item_type_index');
                }
                if ($indexExists('glasses_stock', 'glasses_stock_quantity_index')) {
                    $table->dropIndex('glasses_stock_quantity_index');
                }
                if ($indexExists('glasses_stock', 'glasses_stock_type_quantity_index')) {
                    $table->dropIndex('glasses_stock_type_quantity_index');
                }
            });
        }

        // Drop stock movements indexes
        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) use ($indexExists) {
                if ($indexExists('stock_movements', 'stock_movements_movement_type_index')) {
                    $table->dropIndex('stock_movements_movement_type_index');
                }
                if ($indexExists('stock_movements', 'stock_movements_stock_date_index')) {
                    $table->dropIndex('stock_movements_stock_date_index');
                }
            });
        }
    }
};
