@extends('outlet.template')
@section('content')
<!-- Content Wrapper START -->
    <div class="main-content">
        <div class="container-fluid">
            <div class="page-title">
                <h4><?php echo $table; ?></h4>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-block">
                            <div class="clearfix">
<!--                                <div class="btn-group">
                                    <a href="{{URL::to('admin/category/add')}}"><button id="editable-sample_new" class="btn btn-primary">Add New <i class="fa fa-plus"></i></button></a>
                                </div>-->
                            </div>
                            <div class="table-overflow">
                                <table id="dt-opt11" class="table table-lg table-hover">
                                    <thead>
                                        <tr>
                                            <th>FEES</th>
                                            <th>USD</th>
                                            <th>JMD</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                               <td align="left">Shipping Cost</td>
                                                <td>$<?php echo $english_format_number = number_format($ship, 2, '.', ',');?></td>
                                                <td>$<?php echo $english_format_number = number_format($ship*$usRate, 2, '.', ',');?></td>
                                            </tr>
                                            <!--<tr>
                                                <td align="left">Item Cost</td>
                                                <td>$<?php echo $english_format_number = number_format($amount, 2, '.', ',');?></td>
                                                <td>$<?php echo $english_format_number = number_format($amount*$usRate, 2, '.', ',');?></td>
                                            </tr>-->
                                            <tr>
                                                <td align="left">Processing Fee</td>
                                                <td>$<?php echo $english_format_number = number_format($proc, 2, '.', ',');?></td>
                                                <td>$<?php echo $english_format_number = number_format($proc*$usRate, 2, '.', ',');?></td>
                                            </tr>
                                            <tr>
                                                <td align="left">Processing Fee GCT</td>
                                                <td>$<?php echo $english_format_number = number_format($gct, 2, '.', ',');?></td>
                                                <td>$<?php echo $english_format_number = number_format($gct*$usRate, 2, '.', ',');?></td>
                                            </tr>
                                            <?php if($amount>50){?>
                                                <tr>
                                                    <td align="left">Import Custom Duty</td>
                                                    <td>$<?php echo $english_format_number = number_format($icd, 2, '.', ',');?></td>
                                                    <td>$<?php echo $english_format_number = number_format($icd*$usRate, 2, '.', ',');?></td>
                                                </tr>
                                                <tr>
                                                    <td align="left">Environmental Levy</td>
                                                    <td>$<?php echo $english_format_number = number_format($enl, 2, '.', ',');?></td>
                                                    <td>$<?php echo $english_format_number = number_format($enl*$usRate, 2, '.', ',');?></td>
                                                </tr>
                                                <tr>
                                                    <td align="left">Standard Compliance Fee</td>
                                                    <td>$<?php echo $english_format_number = number_format($scf, 2, '.', ',');?></td>
                                                    <td>$<?php echo $english_format_number = number_format($scf*$usRate, 2, '.', ',');?></td>
                                                </tr>
                                                <tr>
                                                    <td align="left">Customs Administration Fee</td>
                                                    <td>$<?php echo $english_format_number = number_format($caf, 2, '.', ',');?></td>
                                                    <td>$<?php echo $english_format_number = number_format($caf*$usRate, 2, '.', ',');?></td>
                                                </tr>
                                                <tr>
                                                    <td align="left">Stamp Duty</td>
                                                    <td>$<?php echo $english_format_number = number_format($sd, 2, '.', ',');?></td>
                                                    <td>$<?php echo $english_format_number = number_format($sd*$usRate, 2, '.', ',');?></td>
                                                </tr>
                                                <tr>
                                                    <td align="left">Import Custom Duty GCT</td>
                                                    <td>$<?php echo $english_format_number = number_format($icdgct, 2, '.', ',');?></td>
                                                    <td>$<?php echo $english_format_number = number_format($icdgct*$usRate, 2, '.', ',');?></td>
                                                </tr>
                                                <tr>
                                                    <td align="left">Total Custom Charges</td>
                                                    <td>$<?php echo $english_format_number = number_format($custCharges, 2, '.', ',');?></td>
                                                    <td>$<?php echo $english_format_number = number_format($custCharges*$usRate, 2, '.', ',');?></td>
                                                </tr>
                                            <?php }?>
                                            <tr>
                                                <td align="left" bgcolor="#FF0000" style="color:#FFF;background-color:#FF0000;"><strong><?php echo $totDesc?></strong></td>
                                                <td  style="color:#FFF; background-color:#FF0000;">$<?php echo $english_format_number = number_format($total, 2, '.', ',');?></td>
                                                <td  style="color:#FFF; background-color:#FF0000;">$<?php echo $english_format_number = number_format($total*$usRate, 2, '.', ',');?></td>
                                            </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Content Wrapper END -->
@endsection