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
<!--<div-->
<!--        style="margin: 0 280px 25px; padding:25px 15px 10px; height: 90px; width: 90px; text-align:center; color: #FF8800; line-height: 18px; font-size: 22px; font-weight: 900; border: 3px solid #FF8800; border-radius: 50%; text-shadow: 3px 3px 2px #ddd; box-shadow: 2px 2px 2px 2px  #ddd inset;">-->
<!--    <i>la</i><br>-->
<!--    D<span style="border-bottom: 1px solid #FF8800">ONN</span>A<br>-->
<!--    <p style="font-size: 12px; line-height: 24px;">Hochzeitsatelier</p>-->
<!--</div>-->
<div class="row" style="bottom: 15px; position: absolute; width: 100%">
    <div class="col-xs-3" style="font-size: 12px; float:left; width: 40%">
        <div style="width: 100%; ">
            <b><?= $answers['name'] ?></b>
            <br><b><?= $username ?></b>
        </div>
    </div>
    <div class="col-xs-3" style="font-size: 10px; width: 20%">
        <div style="width: 100%; ">
            Protokoll vom
            <br><?= date('d.m.Y', $answers['start_date']) ?>
        </div>
    </div>
</div>
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
<div class="container-fluid" style="top: 150px">
    <div class="row">
        <div class="col-md-7" style="font-size: 12px; height: 30px;">
            <span><b>Protokoll zur <?= $answers['name'] ?>:</b></span> <?= $audit ?>
        </div>
    </div>
    <table class="table" style="text-align: left; margin-top: 40px">
        <thead>
        <tr>
            <th style="width: 100px; padding-bottom: 5px; text-align: left; border-bottom: 1px solid #ddd;">Vorgang:</th>
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
            <!--            --><? // ++$iterator; ?>
            <tr>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?= $iterator++ . '.' ?>
                </td>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?= date('H:i:s', $answer['start_date']) ?>
                </td>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?= 'something Aufforderung' ?>
                </td>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?= isset($answer['question'])?$answer['question']:"" ?>
                </td>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?= isset($answer['answer'])?$answer['answer']:"" ?>
                </td>
                <td style=" height: 35px; border-bottom: 1px solid #ddd;">
                    <?= isset($answer['data'])?$answer['data']:"" ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td></td>
            <td style=" height: 50px;"></td>
            <td></td>
            <td></td>
        </tr>
        </tbody>
    </table>

</div>

<footer style="">
    <div class="row" style="font-size: 10px;">
        <div class="col-xs-3" style="float:left; width: 50%">
            <div style="width: 80%; ">
                <img  src="<?= $signature['0'] ?>" >
                <br>Unterschrift Absender
            </div>

        </div>
        <div class="col-xs-3" style="float:right; width: 50%">
            <div style="width: 80%; float: right">
                <img style="width: 100%" src="<?= $signature['1'] ?>" >
                <br> Unterschrift Frachtführer
            </div>
        </div>
    </div>
</footer>

<!--<div style= "position: absolute; left: 50px; bottom: 50px; width: 100%;">-->
<!--  -->
<!--</div>-->

</body>