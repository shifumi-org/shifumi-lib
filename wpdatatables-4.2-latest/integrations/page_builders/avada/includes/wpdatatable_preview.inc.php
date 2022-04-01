<script type="text/template" id="fusion_builder_block_wpdatatable_preview_template">
    <h4 class="fusion_module_title wpdatatable-head-title">
        <span class="fusion-module-icon {{ fusionAllElements[element_type].icon }}"></span>
        {{ fusionAllElements[element_type].name }}</h4>
    <#
    var selectedTable = params.id;
    var selectedTableName = fusionAllElements[element_type].params.id.value[params.id];
    var selectedView = params.table_view;
    var selectedVAR1 = params.var1;
    var selectedVAR2 = params.var2;
    var selectedVAR3 = params.var3;
    var selectedExportFileName = params.export_file_name;

    if (selectedTable && selectedTable != 0 ) {  #>
    <p class="wpdatatable-title">Table: {{{ selectedTableName }}} </p>
    <span class="wpdatatable-id">[wpdatatable id=</span><span>{{{ selectedTable }}}</span>
    <# if (selectedView) {  #>
    <span class="wpdatatable-view"> table_view=</span><span>{{{ selectedView }}}</span>
    <# }  #>
    <# if (selectedVAR1) {  #>
    <span class="wpdatatable-var1"> var1=</span><span>{{{ selectedVAR1 }}}</span>
    <# }  #>
    <# if (selectedVAR2) { #>
    <span class="wpdatatable-var2"> var2=</span><span>{{{ selectedVAR2 }}}</span>
    <# }  #>
    <# if (selectedVAR3) {  #>
    <span class="wpdatatable-var3"> var3=</span><span>{{{ selectedVAR3 }}}</span>
    <# }  #>
    <# if (selectedExportFileName) {  #>
    <span class="wpdatatable-export-file-name"> export_file_name=</span><span>{{{ selectedExportFileName }}}</span>
    <# }  #>
    <span>]</span>
    <# } else {  #>
    <span class="wpdatatable-no-id">Please choose wpDatatable ID.</span>
    <# } #>
</script>