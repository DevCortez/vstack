<template>
    <tr>
        <td colspan="2" :style="{ height: h }">
            <div class="d-flex flex-column mb-3">
                <span class="input-title" v-if="label" v-html="label ? label : ''" />
                <small v-if="description" class="mt-1 text-muted">
                    <span v-html="description"></span>
                </small>
            </div>
            <GrapesEditor :errors="errors" :mode="mode" :blocks="blocks" :settings="settings" v-model="content" :height="h" />
        </td>
    </tr>
</template>
<script>
export default {
    props: ["description", "label", "errors", "mode", "blocks", "settings", "value", "height"],
    data() {
        return {
            content: this.value,
        };
    },
    computed: {
        h() {
            return this.height ? this.height : 1000;
        },
    },
    watch: {
        content(val) {
            this.$emit("input", val);
        },
    },
};
</script>
