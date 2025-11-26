<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_marks', function (Blueprint $table) {
            // Your existing column addition code remains the same...
            if (!Schema::hasColumn('exam_marks', 'exam_submission_id')) {
                $table->unsignedBigInteger('exam_submission_id')->nullable()->after('id');
            }

            $columnsToAdd = [
                'class_id' => 'unsignedBigInteger',
                'teacher_id' => 'unsignedBigInteger',
                'exam_id' => 'unsignedBigInteger',
                'maximum_marks' => 'decimal',
                'grade' => 'string',
                'rejection_reason' => 'longText',
                'submitted_by' => 'unsignedBigInteger',
                'submitted_at' => 'timestamp',
                'approved_by' => 'unsignedBigInteger',
                'approved_at' => 'timestamp',
                'rejected_by' => 'unsignedBigInteger',
                'rejected_at' => 'timestamp',
            ];

            foreach ($columnsToAdd as $column => $type) {
                if (!Schema::hasColumn('exam_marks', $column)) {
                    if ($type === 'decimal') {
                        $table->decimal($column, 10, 2)->nullable();
                    } elseif ($type === 'timestamp') {
                        $table->timestamp($column)->nullable();
                    } else {
                        $table->$type($column)->nullable();
                    }
                }
            }
        });

        // Add indexes using separate statements with existence checks
        $this->safeAddIndex('exam_marks', 'status');
        $this->safeAddIndex('exam_marks', 'submitted_at');
        $this->safeAddIndex('exam_marks', ['exam_id', 'class_id']);
        $this->safeAddIndex('exam_marks', ['student_id', 'exam_subject_id']);
    }

    public function down(): void
    {
        Schema::table('exam_marks', function (Blueprint $table) {
            // Your existing column dropping code remains the same...
            $columnsToDrop = [
                'exam_submission_id',
                'class_id',
                'teacher_id',
                'exam_id',
                'maximum_marks',
                'grade',
                'rejection_reason',
                'submitted_by',
                'submitted_at',
                'approved_by',
                'approved_at',
                'rejected_by',
                'rejected_at',
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('exam_marks', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        // Drop indexes safely
        $this->safeDropIndex('exam_marks', 'exam_marks_status_index');
        $this->safeDropIndex('exam_marks', 'exam_marks_submitted_at_index');
        $this->safeDropIndex('exam_marks', 'exam_marks_exam_id_class_id_index');
        $this->safeDropIndex('exam_marks', 'exam_marks_student_id_exam_subject_id_index');
    }

    private function safeAddIndex(string $table, $columns): void
    {
        $indexName = is_array($columns) 
            ? $table . '_' . implode('_', $columns) . '_index'
            : $table . '_' . $columns . '_index';

        if (!$this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($columns, $indexName) {
                $table->index($columns, $indexName);
            });
        }
    }

    private function safeDropIndex(string $table, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            Schema::table($table, function (Blueprint $table) use ($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return DB::select("
            SELECT COUNT(*) as count 
            FROM information_schema.statistics 
            WHERE table_schema = ? 
            AND table_name = ? 
            AND index_name = ?
        ", [DB::getDatabaseName(), $table, $indexName])[0]->count > 0;
    }
};