<?php

//echo $message;

/*
array (
    'id' => 'b748e3b0-b813-48bb-8c7c-9f803ef711e7',
    'authEventId' => 'f26bd33e-448f-4976-a59f-8e1153af4d39',
    'tenantId' => 'eafd3b39-46c7-41e4-ba4e-6ea6685e39f7',
    'tenantType' => 'ORGANISATION',
    'tenantName' => 'Cabin King Hamilton',
    'createdDateUtc' => '2023-08-24T04:59:51.5706280',
    'updatedDateUtc' => '2023-08-24T04:59:51.5728370',
  ),
  */

  ?>
<div class='row'>
    <div class="col-md-12 col-xl-6">
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title">You have access to these companies</h5>
            </div>
            <div class="card-body">
            <?php foreach ($xeroTenantIdArray as $row): ?>
                <div class="clearfix row mb-4">
                    <div class="col">
                        <div class="float-start">
                            <h5 class="mb-0"><strong> <?= $row['tenantName']; ?></strong></h5>
                           
                        </div>
                    </div>
                    <div class="col">
                        <div class="float-end">
                            <small class="text-blue"><?= $row['tenantId']; ?></small>
                        </div>
                    </div>
                </div>
            
            <?php endforeach; ?></div>
        </div>
    </div>
</div>