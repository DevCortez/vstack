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
                <el-radio-group :disabled="disabled" v-model="val" v-bind:class="{ 'is-invalid': errors }">
                    <el-radio-button
                        v-for="(op, i) in option_list"
                        :key="i"
                        :label="op.value ? op.value : op"
                        v-bind:class="{ 'option-selected': val == (op.value ? op.value : op) }"
                    >
                        <div v-html="op.label ? op.label : op" />
                    </el-radio-button>
                </el-radio-group>
                <div class="invalid-feedback" v-if="errors">
                    <ul class="pl-3 mb-0">
                        <li v-for="(e, i) in errors" :key="i" v-html="e" />
                    </ul>
                </div>
            </div>
        </td>
    </tr>
</template>
<script>
export default {
    props: ["label", "errors", "disabled", "option_list", "description"],
    data() {
        return {
            val: null,
        };
    },
    watch: {
        val(val) {
            return this.$emit("input", val);
        },
    },
    created() {
        setTimeout(() => {
            if (!this._isDestroyed) {
                this.val = this.$attrs.value;
            }
        });
    },
};
</script>
