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
    var selectedVAR4 = params.var4;
    var selectedVAR5 = params.var5;
    var selectedVAR6 = params.var6;
    var selectedVAR7 = params.var7;
    var selectedVAR8 = params.var8;
    var selectedVAR9 = params.var9;
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
    <# if (selectedVAR4) {  #>
    <span class="wpdatatable-var4"> var4=</span><span>{{{ selectedVAR4 }}}</span>
    <# }  #>
    <# if (selectedVAR5) {  #>
    <span class="wpdatatable-var5"> var5=</span><span>{{{ selectedVAR5 }}}</span>
    <# }  #>
    <# if (selectedVAR6) {  #>
    <span class="wpdatatable-var6"> var6=</span><span>{{{ selectedVAR6 }}}</span>
    <# }  #>
    <# if (selectedVAR7) {  #>
    <span class="wpdatatable-var7"> var7=</span><span>{{{ selectedVAR7 }}}</span>
    <# }  #>
    <# if (selectedVAR8) {  #>
    <span class="wpdatatable-var8"> var8=</span><span>{{{ selectedVAR8 }}}</span>
    <# }  #>
    <# if (selectedVAR9) {  #>
    <span class="wpdatatable-var9"> var9=</span><span>{{{ selectedVAR9 }}}</span>
    <# }  #>
    <# if (selectedExportFileName) {  #>
    <span class="wpdatatable-export-file-name"> export_file_name=</span><span>{{{ selectedExportFileName }}}</span>
    <# }  #>
    <span>]</span>
    <# } else {  #>
    <span class="wpdatatable-no-id">Please choose wpDatatable ID.</span>
    <# } #>
</script>