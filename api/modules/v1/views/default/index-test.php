<?php

/* @var $this yii\web\View */

$totalCost = 0;
$iterator = 1;


?>
<head>
    <link rel="stylesheet" href="/vendor/bower/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/vendor/bower/bootstrap/css/mdb.min.css">
</head>
<body style="background-color: #FFF;">

<div style="
    float: right;
    height: 150px;
    width: 175px;
    position: absolute;
    top: 15px;
    right: 50px;
    line-height: 1px;">
    <img style="width: 100%" src="/files/index.jpg" alt="Mountain View">
</div>
<div class="container-fluid" style="
    float: left;
    position: absolute;
    top: 80px;
    left: 40px;
    line-height: 1px;">
    <div class="row">
        <div class="col-md-7" style="font-size: 12px; height: 30px;">
            <span><b>Protokoll zur <?= $answers['type'] ?>:</b></span> <?= $audit ?>
        </div>
    </div>
</div>
<div style="top:100px; margin-top: 40px">
    <table class="table" style="text-align: left">
        <thead>
        <tr>
            <th style="width: 100px; padding-bottom: 5px; text-align: left; border-bottom: 1px solid #ddd;">Vorgang:
            </th>
            <th style="width: 100px; padding-bottom: 5px; text-align: left; border-bottom: 1px solid #ddd;">Uhrzeit:
            </th>
            <th style="width: 150px; padding-bottom: 5px; text-align: left; border-bottom: 1px solid #ddd;">Prozess:
            </th>
            <th style="width: 200px; padding-bottom: 5px; text-align: left; border-bottom: 1px solid #ddd;">
                Verfahrensbeschreibung:
            </th>
            <th style="width: 100px; padding-bottom: 5px; text-align: left; border-bottom: 1px solid #ddd;">Antwort:
            </th>
            <th style="width: 150px; padding-bottom: 5px; text-align: left; border-bottom: 1px solid #ddd;">Vermerk:
            </th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($answers['kriterien'] as $answer): ?>
            <tr>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?= $iterator++ . '.' ?>
                </td>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?php
                    $startDate = explode(' ', $answer['start_date']);
                    echo $startDate['1'];
                    ?>
                </td>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?php if ($answer['process_type'] == 1) {
                        echo 'Daten';
                    } elseif ($answer['process_type'] == 2) {
                        echo 'Ja/Nein';
                    } elseif ($answer['process_type'] == 3) {
                        echo 'Foto';
                    } elseif ($answer['process_type'] == 4) {
                        echo 'Unterschrift';
                    } ?>
                </td>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?= isset($answer['question']) ? $answer['question'] : "" ?>
                </td>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?= isset($answer['answer']) ? $answer['answer'] : "" ?>
                </td>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?= isset($answer['data']) ? $answer['data'] : "" ?>
                </td>
            </tr>
        <?php endforeach; ?>

        </tbody>
    </table>
    <div style="width: 100%;">
        <div style="width: 50%;float: left">

                <img style="margin: 15px auto; width: 300px" src="<?= $signature['0'] ?>">
                <br>Unterschrift Absender

        </div>
        <div style="width: 50%;float: left;">

                <img style="margin: 15px 0 15px 60px; width: 300px" src="<?= $signature['1'] ?>">
                <br><div style="padding-left:60px">Unterschrift Frachtführer</div>

        </div>
    </div>

</div>


</body>