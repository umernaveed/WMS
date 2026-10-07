<div class="table-overflow">
    <table class="table table-hover">
        <thead>
        <tr>
            <th>HAWB.</th>
            <th>Supplier Tracking No</th>
            <th>Supplier</th>
            <th>Date Created</th>
            <th>Description</th>
            <th>Name</th>
            <th>Package Amount</th>
            <th>Shipment Status</th>
    
        </tr>
        </thead>
        <tbody>
            @if($packages)
                @foreach($packages as $row)
                    <tr>
                        <?php $packageInvoice = commonHelper::getPackageAmount($row->tracking_no); ?>
                        <td>{{$row->manifest_no}}</td>
                        <td>{{$row->supplier_tracking_no}}</td>
                        <td>{{$row->courier}}</td>
                        <td>{{date('d M Y' , strtotime($row->created_at))}}</td>
                        <td>{{$row->item_description}}</td>
                        <td>{{$row->first_name}}</td>
                        <td align="right">{{$packageInvoice}}</td>
                        <td>{{$row->name}}</td>
                    </tr>
                @endforeach
            @endif
            
        </table>
    </div>