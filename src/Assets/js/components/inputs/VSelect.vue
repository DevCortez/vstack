<template>
    <tr>
        <td class="w-25">
            <div class="d-flex flex-column">
                <span class="input-title" v-if="label" v-html="label ? label : ''" />
                <small v-if="description" class="mt-1 text-muted">
                    <span v-html="description"></span>
                </small>
            </div>
        </td>
        <td>
            <div class="d-flex flex-column">
                <div class="input-group v-select" v-bind:class="{ 'is-invalid': errors }">
                    <template v-if="multiple">
                        <template v-if="loading">
                            <div class="shimmer select mb-3" />
                            <div class="shimmer select mb-3" />
                            <div class="shimmer select mb-3" />
                        </template>
                        <div class="d-flex flex-row" v-else>
                            <el-checkbox-group class="vstack-hasmany" v-model="value" >
                                <el-checkbox-button v-for="(op, i) in options" :label="op.id" :key="i">
                                    {{ op.name }}
                                </el-checkbox-button>
                            </el-checkbox-group>
                            <a class="px-3 text-center f-12" @click.prevent="toggleMarked" href="#">
                                {{!marked ?'Marcar':'Desmarcar'}} todas as opções
                            </a>
                        </div>
                    </template>
                    <template v-else>
                        <div class="shimmer select" v-if="loading" />
                        <el-select
                            :allow-create="allowcreate"
                            :disabled="disabled"
                            v-else
                            :size="size ? size : 'large'"
                            class="w-100"
                            clearable
                            v-model="value"
                            filterable
                            :placeholder="placeholder"
                            v-loading="loading"
                            :loading="loading"
                            loading-text="Carregando..."
                            :popper-append-to-body="false"
                        >
                            <el-option v-for="(item, i) in options" :key="i" :label="item.name" :value="String(item.id)">
                                <div class="w-100 d-flex" v-html="item.name"></div>
                            </el-option>
                        </el-select>
                    </template>
                    <div class="invalid-feedback" v-if="errors">
                        <ul class="pl-3 mb-0">
                            <li v-for="(e, i) in errors" :key="i" v-html="e" />
                        </ul>
                    </div>
                </div>
            </div>
        </td>
    </tr>
</template>
<script>
export default {
    props: [
        "placeholder",
        "label",
        "route_list",
        "list_model",
        "disabled",
        "errors",
        "optionlist",
        "required",
        "size",
        "multiple",
        "relation",
        "allowcreate",
        "option_list",
        "description",
        "all_options_label",
        "model_fields",
    ],
    data() {
        return {
            loading: true,
            started: false,
            options: [],
            value: this.multiple ? [] : null,
            marked:false
        };
    },
    created() {
        setTimeout(() => {
            if (!this._isDestroyed) {
                this.initialize();
            }
        });
    },
    watch: {
        value(val) {
            if (this.started) {
                this.$emit("input", val);
                if(Array.isArray(this.value)) {
                    this.marked=this.value.length === this.options.length
                }
            }
        },
    },
    methods: {
        toggleMarked() {
            if(!this.marked) {
                this.value = this.options.map(x=>x.id);
            } else {
                this.value = [];
            }
            this.marked = !this.marked;
        },
        initialize() {
            if (this.list_model) {
                this.initOptions(() => {
                    this.value = this.$attrs.value ? this.$attrs.value : this.multiple ? [] : null;
                    if(!Array.isArray(this.value)) {
                        this.value = String(this.value);
                    } 
                    this.loading = false;
                    this.started = true;
                });
            } else {
                for (let i in this.option_list) {
                    let value = this.option_list[i];
                    if (typeof value == "string") {
                        this.options.push({ id: value, name: value });
                    }
                    if (typeof value == "object") {
                        this.options.push({ id: String(value.id), name: value.value });
                    }
                }
                this.value = this.$attrs.value ? this.$attrs.value : this.multiple ? [] : null;
                if(Array.isArray(this.value)) {
                    this.value = this.value.map(x=>String(x));
                } 
                this.loading = false;
                this.started = true;
            }
        },
        initOptions(callback) {
            if (this.optionlist || !this.list_model) {
                this.options = this.optionlist ? this.optionlist : [];
                return callback();
            }
            this.$http
                .post(this.route_list, { model: this.list_model, model_fields: this.model_fields })
                .then((res) => {
                    res = res.data;
                    this.options = res.data.map(x => {
                        return { id: String(x.id), name: x.name };
                    });
                    callback();
                })
                .catch((er) => {
                    this.loading = false;
                    console.log(er);
                });
        },
    },
};
</script>
<style scoped lang="scss">
.shimmer.select {
    height: 37px;
    width: 100%;
    border-radius: 5px;
}
.v-select {
    &.is-invalid {
        .el-select {
            border: 1px solid red;
        }
        .invalid-feedback {
            display: block;
        }
    }
}
</style>