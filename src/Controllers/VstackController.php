<?php

namespace marcusvbda\vstack\Controllers;

use App\Http\Controllers\Controller;
use ResourcesHelpers;
use Illuminate\Http\Request;

class VstackController extends Controller
{
	public function getPartialContent($resource, Request $request)
	{
		$resource = ResourcesHelpers::find($resource);
		return  $this->{$request["type"]}($resource, $request);
	}

	// protected function listAllInOne($resource, Request $request)
	// {
	// 	$rows = $resource->getModelInstance()->whereIn("id", $request->ids)->get();
	// 	$data = [];
	// 	foreach ($rows as $row) {
	// 		$row_data = $this->resourceTableContent($resource, $request, $row, true, true);
	// 		$data[] = $row_data;
	// 	}
	// 	return $data;
	// }

	public function resourceTableContent($resource, $request, $row = null, $force_id = false, $include_after_row = false)
	{
		if (@!$row) {
			$row = $resource->useRawContentOnList() ? (object)$request['raw_content'] : $resource->model->findOrFail($request["row_id"]);
		}
		$content = [];
		if (@$request["complete_model"]) {
			$content = $row;
		} else {
			if ($force_id) {
				$content["id"] = $row->id;
				$content["code"] = $row->code;
			}
			if ($include_after_row) {
				$content["after_row"] = $resource->tableAfterRow($row);
			}
			if ($resource->useTags()) {
				$content["tags"] = $row->tags;
			}
			foreach ($resource->table() as $key => $value) {
				if (!@$value["handler"]) {
					if (strpos($key, "->") === false) {
						$content[$key] = @$row->{$key} !== null ? $row->{$key} : " - ";
					} else {
						$value = $row;
						$_runner = explode("->", $key);
						foreach ($_runner as $idx) {
							$value = @$value->{$idx};
						}
						$content[$key] = (@$value !== null ? $value : ' - ');
					}
				} else {
					$result = @$value["handler"]($row);
					$content[$key] = (@$result !== null ? $result : ' - ');
				}
			}
		}
		$acl = [
			"can_update" => $resource->checkAclResource($row, "update"),
			"can_clone" => $resource->checkAclResource($row, "clone"),
			"can_delete" => $resource->checkAclResource($row, "delete"),
			"can_view" => $resource->checkAclResource($row, "view"),
			"use_tags" => $resource->useTags(),
			"resource_singular_label" => $resource->singularLabel(),
			"resource_label" => $resource->label(),
			"resource_icon" => $resource->icon(),
			"crud_type" => $resource->crudType(),
			"before_delete" => array_map(function ($row) {
				unset($row["handler"]);
				return $row;
			}, @$resource->beforeDelete() ?? [])
		];
		return ["content" => $content, "acl" => $acl];
	}

	public function getColumnIndex($columns, $row, $key, $placeholder = "          -          ")
	{
		$removeEmoji = function ($text) {
			$clean_text = "";
			$regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
			$clean_text = preg_replace($regexEmoticons, '', $text);
			$regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
			$clean_text = preg_replace($regexSymbols, '', $clean_text);
			$regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
			$clean_text = preg_replace($regexTransport, '', $clean_text);
			$regexMisc = '/[\x{2600}-\x{26FF}]/u';
			$clean_text = preg_replace($regexMisc, '', $clean_text);
			$regexDingbats = '/[\x{2700}-\x{27BF}]/u';
			$clean_text = preg_replace($regexDingbats, '', $clean_text);
			return $clean_text;
		};
		$value = "";
		$handler = data_get($columns, $key . ".handler");
		if ($handler) {
			$value = $removeEmoji(trim($handler($row)));
		}
		if (@trim($value) === "") {
			$value = null;
		}
		return (@$value !== null ? $value : $placeholder);
	}

	public function getJson(Request $request)
	{
		$model = @$request["model"];
		if (!$model) {
			abort(400);
		}
		$per_page = @$request["per_page"];
		$includes = @$request["includes"] ?? [];
		$fields = @$request["fields"] ?? ["*"];
		$order_by = @$request["order_by"];
		$query = app()->make($model);
		$filters  = @$request["filters"] ?? [];
		$query = $this->processApiFilters($filters, $query);
		$result = $query->select($fields)->with($includes);
		if ($order_by) {
			$query = $query->orderBy($order_by[0], $order_by[1]);
		}
		return $per_page ? $result->paginate($per_page) : $result->get();
	}

	private function processApiFilters($filters, $query)
	{
		foreach ($filters as $filter_type => $filters) {
			if ($filter_type == "where") $query = $query->where($filters);
			if ($filter_type == "or_where") {
				$query = $query->where(function ($q) use ($filters) {
					foreach ($filters as $filter) {
						$q->orWhere([$filter]);
					}
				});
			}
			if ($filter_type == "or_where_in") {
				$query = $query->where(function ($q) use ($filters) {
					foreach ($filters as $filter) {
						$q->orWhereIn([$filter]);
					}
				});
			}
			if ($filter_type == "or_where_not_in") {
				$query = $query->where(function ($q) use ($filters) {
					foreach ($filters as $filter) {
						$q->orWhereNotIn($filter[0], $filter[1]);
					}
				});
			}
			if ($filter_type == "where_in") {
				$query = $query->where(function ($q) use ($filters) {
					foreach ($filters as $filter) {
						$q->whereIn($filter[0], $filter[1]);
					}
				});
			}
			if ($filter_type == "where_in") {
				$query = $query->where(function ($q) use ($filters) {
					foreach ($filters as $filter) {
						$q->whereIn($filter[0], $filter[1]);
					}
				});
			}
			if ($filter_type == "raw_where") {
				$query = $query->where(function ($q) use ($filters) {
					foreach ($filters as $filter) {
						$q->whereRaw($filter);
					}
				});
			}
		}
		return $query;
	}

	public function grapesEditor()
	{
		return view("vStack::resources.field.grapes.iframe");
	}
}
