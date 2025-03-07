@php
$crud_buttons = [
    "code"         => null,
    "can_view"     => $resource->canView(),
    "can_update"   => $resource->canUpdate(),
    "can_delete"   => $resource->canDelete(),
    "route"        => null
];

$list_types = @$resource->listType() ? $resource->listType() : ["table"];
$table = $resource->exportColumns();
$table_keys = array_keys($table);
$controller  = new  \marcusvbda\vstack\Controllers\VstackController;
@endphp
@include($resource->vieReportBlade())