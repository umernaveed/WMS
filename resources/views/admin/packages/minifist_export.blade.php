
<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<Awbolds>
    <Master_bol>
        <Customs_office_code>{{ $manifest_info->custom_office_code }}</Customs_office_code>
        <Voyage_number>{{ $manifest_info->shipping_agent_code }}</Voyage_number>
        <Date_of_departure>{{ $manifest_info->date_of_departure }}</Date_of_departure>
        <Reference_number>{{ $manifest_info->master_air_billing_no }}</Reference_number>
    </Master_bol>
    @if( $packages )
        @foreach( $packages as $line => $row )
            <Bol_segment>
                <Bol_id>
                    <Bol_reference>{{ $row->package->tracking_no }}</Bol_reference>
                    <Line_number>{{ $line + 1 }}</Line_number>
                    <Bol_nature>23</Bol_nature>
                    <Bol_type_code>AWB</Bol_type_code>
                </Bol_id>
                <Transport_information>
                    <Carrier>
                        <Carrier_code>{{ $manifest_info->carrier_code }}</Carrier_code>
                    </Carrier>
                    <Shipping_Agent>
                        <Shipping_Agent_code>{{ $manifest_info->shipping_agent_code }}</Shipping_Agent_code>
                    </Shipping_Agent>
                    <Vessel_loading_code>{{ $manifest_info->loading_port }}</Vessel_loading_code>
                    <Vessel_discharge_code>{{ $manifest_info->custom_office_code }}</Vessel_discharge_code>
                </Transport_information>
                <Load_unload_place>
                    <Place_of_loading_code>{{ $manifest_info->loading_port }}</Place_of_loading_code>
                    <Place_of_unloading_code>{{ $manifest_info->custom_office_code }}</Place_of_unloading_code>
                </Load_unload_place>
                <Traders_segment>
                    <Exporter>
                        <Exporter_name>{{ $manifest_info->cargo_company }}</Exporter_name>
                        <Exporter_address>{{ $manifest_info->exporter_address }}</Exporter_address>
                    </Exporter>
                    <Consignee>
                        <Consignee_name>{{ $row->package->user_name }}</Consignee_name>
                        <Consignee_address>21 Molynes Road, Kingston 10, Jamaica</Consignee_address>
                    </Consignee>
                </Traders_segment>
                <Goods_segment>
                    <Number_of_packages>1</Number_of_packages>
                    <Package_type_code>BG</Package_type_code>
                    <Gross_mass>{{ $row->weight }}</Gross_mass>
                    <Shipping_marks>Shipping Marks</Shipping_marks>
                    <Goods_description>Courier Goods</Goods_description>
                    <Volume_in_cubic_meters>0</Volume_in_cubic_meters>
                    <Num_of_ctn_for_this_bol>0</Num_of_ctn_for_this_bol>
                </Goods_segment>
                <Value_segment>
                    <Freight_segment>
                        <PC_indicator>PP</PC_indicator>
                        <Freight_value>0</Freight_value>
                        <Freight_currency>USD</Freight_currency>
                    </Freight_segment>
                </Value_segment>
            </Bol_segment>
        @endforeach
    @endif
</Awbolds>