<template>
    <div class="card">
        <template v-if="data.resource.import_custom_map_step">
            <div class="card-header bg-white py-4" v-if="data.resource.import_custom_map_step.subtitle">
                <div class="row">
                    <div class="col-12">
                        <h3 v-html="data.resource.import_custom_map_step.title" />
                        <div class="mt-3" v-html="data.resource.import_custom_map_step.subtitle" />
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <v-runtime-template :template="data.resource.import_custom_map_step.template" />
                    </div>
                </div>
            </div>
        </template>
        <template v-else>
            <div class="card-header bg-white py-4">
                <div class="row">
                    <div class="col-12">
                        <h3>Mapear campos da planilha para produtos {{ data.resource.label.toLowerCase() }}</h3>
                        <div class="mt-3">
                            Selecione os campos da sua planilha (a esquerda) e relacione-os com os campos que deverão ser
                            importados em {{ data.resource.label.toLowerCase() }} (a direita)
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-12">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th class="px-5">Nome da coluna</th>
                                    <th class="px-5">Mapear para o campo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(column, i) in config.data.csv_header" :key="i">
                                    <template>
                                        <td class="px-5">{{ column }}</td>
                                        <td class="px-5">
                                            <el-select
                                                class="w-100"
                                                clearable
                                                v-model="config.fieldlist[column]"
                                                filterable
                                                placeholder="Seleciona para onde este campo será importado"
                                            >
                                                <el-option label="Ignorar" value="_IGNORE_" />
                                                <el-option
                                                    v-for="(item, i) in headerOptions"
                                                    :key="i"
                                                    :label="item"
                                                    :value="item"
                                                    :disabled="columnHasSelected(item)"
                                                />
                                            </el-select>
                                        </td>
                                    </template>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </template>
        <div class="card-footer bg-white">
            <div class="row">
                <div class="col-12 d-flex flex-row flex-wrap align-items-center justify-content-end">
                    <button class="btn btn-primary" @click="next" :disabled="canExecute">Executar Importador</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import VRuntimeTemplate from "v-runtime-template";

export default {
    props: ["data", "frm", "config"],
    data() {
        return {
            step_data: {
                can_next: false,
            },
        };
    },
    components: {
        "v-runtime-template": VRuntimeTemplate,
    },
    computed: {
        headerOptions() {
            return [].concat(this.config.data.columns);
        },
        canExecute() {
            if (this.data.resource.import_custom_map_step) {
                return !this.step_data.can_next;
            }
            return Object.keys(this.config.fieldlist).length < this.config.data.csv_header.length;
        },
    },
    mounted() {
        this.config.fieldlist = {};
        this.relateColumns();
    },
    methods: {
        columnHasSelected(item) {
            let headers = this.config.data.csv_header;
            let selected = headers.find((header) => {
                return this.config.fieldlist[header] == item;
            });
            return selected ? true : false;
        },
        relateColumns() {
            let columns = this.headerOptions;
            let headers = this.config.data.csv_header;
            for (let i in headers) {
                let index = columns.indexOf(headers[i]);
                if (index > -1) {
                    this.$set(this.config.fieldlist, columns[index], headers[i]);
                }
            }
        },
        next() {
            this.loading = true;
            this.config.step += 2;
        },
    },
};
</script>
