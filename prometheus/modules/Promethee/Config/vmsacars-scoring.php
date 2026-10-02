<?php

/*
 * Historical vmsACARS scoring policy kept as a first-party Prométhée fallback.
 *
 * When the legacy VMSAcars module is enabled, HermesScoringService reads the
 * administrator-managed vmsacars_rules table instead. This copy guarantees
 * that Hermès still scores PIREPs when the legacy module is absent.
 */
return [
    'rules' => [
        ['id'=>'EXCESS_TAXI_SPEED','name'=>'Excess Taxi Speed','parameter'=>30,'points'=>5,'repeatable'=>true,'delay'=>30,'cooldown'=>60,'enabled'=>true,'order'=>30],
        ['id'=>'EXCESS_GFORCE','name'=>'Excess G-Forces','parameter'=>1.5,'points'=>5,'repeatable'=>true,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>35],
        ['id'=>'FUEL_REFILLED','name'=>'Fuel Refilled','parameter'=>null,'points'=>10,'repeatable'=>true,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>40],
        ['id'=>'OVERSPEED_WARNING','name'=>'Overspeed','parameter'=>null,'points'=>2,'repeatable'=>true,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>45],
        ['id'=>'EXCESS_BANK','name'=>'Excess Bank (degrees)','parameter'=>60,'points'=>2,'repeatable'=>true,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>50],
        ['id'=>'EXCESS_PITCH','name'=>'Excess Pitch (degrees)','parameter'=>30,'points'=>2,'repeatable'=>true,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>55],
        ['id'=>'RUNWAY_OVERRUN','name'=>'Runway Overrun','parameter'=>null,'points'=>10,'repeatable'=>false,'delay'=>0,'cooldown'=>0,'enabled'=>true,'order'=>60],
        ['id'=>'SIMRATE_INCREASED','name'=>'Simulation Rate Increased','parameter'=>1,'points'=>2,'repeatable'=>true,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>65],
        ['id'=>'SLEW_ACTIVATED','name'=>'Slew Activated','parameter'=>null,'points'=>2,'repeatable'=>true,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>70],
        ['id'=>'SPEED_UNDER_10K','name'=>'Overspeed under 10k','parameter'=>null,'points'=>2,'repeatable'=>true,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>75],
        ['id'=>'STABILIZED_APPROACH','name'=>'Stabilized Approach','parameter'=>1500,'points'=>5,'repeatable'=>false,'delay'=>0,'cooldown'=>0,'enabled'=>true,'order'=>80],
        ['id'=>'STALL_WARNING','name'=>'Stall Warning','parameter'=>null,'points'=>5,'repeatable'=>true,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>85],
        ['id'=>'THRUST_REVERSERS_INFLIGHT','name'=>'Thrust reversers enabled in flight','parameter'=>null,'points'=>2,'repeatable'=>true,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>90],
        ['id'=>'THRUST_REVERSERS_SPEED','name'=>'Thrust reverser speed','parameter'=>60,'points'=>10,'repeatable'=>false,'delay'=>10,'cooldown'=>60,'enabled'=>true,'order'=>95],
        ['id'=>'HARD_LANDING','name'=>'Hard Landing','parameter'=>500,'points'=>20,'repeatable'=>false,'delay'=>0,'cooldown'=>0,'enabled'=>true,'order'=>100],
    ],
];
