<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id('id');

            $table->date('start_date');
            $table->date('end_date');
            $table->string('days', 192);
            $table->text('reason')->nullable();
            $table->string('attachment', 192)->nullable();
            $table->boolean('half_day')->nullable();
            $table->string('status', 192);

            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('company_id');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('leave_type_id');

            $table->timestamps(6);
            $table->softDeletes();

            $table->foreign('company_id', 'leave_company_id')->references('id')->on('companies');
            $table->foreign('department_id', 'leave_department_id')->references('id')->on('departments');
            $table->foreign('employee_id', 'leave_employee_id')->references('id')->on('employees');
            $table->foreign('leave_type_id', 'leave_leave_type_id')->references('id')->on('leave_types');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('leaves');
    }

}
