@extends('admin.template')
@section('content')

<style>
.radio, .checkbox{
	padding-left:0px;
}
.icheck div, .icheck .disabled {
    float: left;
}
.icheck .single-row {
    display: inline-block;
    width: 100%;
}
</style>
<div class="row">
    <div class="col-lg-12">     
     @include('common.errors')
         <section class="panel">
                <header class="panel-heading">
                    <?php echo $table; ?> <strong style="font-size: 9px;color: #ccc;">please see your quote below</strong>
                </header>
                <div class="panel-body">
                    <div class="position-center" style="width:80%;">
<!--                        <table border='0' width="100%" cellpadding='100' style='border: solid  #ccc 10px;'>
                            <tr>
                                <td>
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>&nbsp;</th>
                                                <th>USD</th>
                                                <th>JMD</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Shipping</td>
                                                <td>$<?php echo $english_format_number = number_format($ship, 2, '.', ',');?></td>
                                                <td>$<?php echo $english_format_number = number_format($ship*128, 2, '.', ',');?></td>
                                            </tr>
                                            <tr>
                                                <td>Invoice Amount</td>
                                                <td>$<?php echo $english_format_number = number_format($amount, 2, '.', ',');?></td>
                                                <td>$<?php echo $english_format_number = number_format($amount*128, 2, '.', ',');?></td>
                                            </tr>
                                            <tr>
                                                <td>Processing</td>
                                                <td>$<?php echo $english_format_number = number_format($proc, 2, '.', ',');?></td>
                                                <td>$<?php echo $english_format_number = number_format($proc*128, 2, '.', ',');?></td>
                                            </tr>
                                            <tr>
                                                <td>GCT</td>
                                                <td>$<?php echo $english_format_number = number_format($gct, 2, '.', ',');?></td>
                                                <td>$<?php echo $english_format_number = number_format($gct*128, 2, '.', ',');?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Total Charges</strong></td>
                                                <td>$<?php echo $english_format_number = number_format($total, 2, '.', ',');?></td>
                                                <td>$<?php echo $english_format_number = number_format($total*128, 2, '.', ',');?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>-->
                        
                        <table class="table table-striped table-hover" >
                            <tr>
                                <td>
                                    <table class="table table-striped table-bordered table-hover dataTables-example">
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
                                </td>
                            </tr>
                        </table>        
                    </div>
                </div>
        </section>
    </div>
</div> 

@endsection 

      