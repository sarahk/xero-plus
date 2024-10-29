<?php

namespace App\Models;


class VehicleModel extends BaseModel
{
    protected string $table = 'vehicles';
    //protected array $joins = [];
    // protected array $virtualFields = [];


    protected string $orderBy = "numberplate ASC";
    protected array $saveKeys = [
        'id',
        'numberplate', 'status', 'notes',
        'xerotenant_id', 'created', 'modified'
    ];
    protected array $updateKeys = ['numberplate', 'notes', 'modified'];

    //protected array $nullable = [];

    protected bool $hasStub = true;
    protected array $orderByColumns = [
        0 => 'vehicles.numberplate.contract_id DIR',
        1 => 'vehicles.status DIR',

    ];


}
