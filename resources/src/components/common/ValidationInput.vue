<script>
import helperMixin from "../../mixins/helperMethods";

export default {
    name: "ValidationInput",
    mixins: [helperMixin],
    props: {
        name: {
            type: String,
            required: true
        },
        rules: {
            type: Object,
            required: true
        }
    },
    mounted() {},
    computed: {
        id() {
            return this.name.toLowerCase().replace(/\s+/g, '-');
        }
    }
}
</script>

<template>
    <validation-provider
        :name="name"
        :rules="{ required: true , regex: /^\d*\.?\d*$/}"
        v-slot="validationContext">
        <b-form-group :label="$t(name) + ' ' + '*'" :id="id">
            <slot :validation-context="validationContext" ></slot>
            <b-form-invalid-feedback :id="`${id}`-feedback">{{ validationContext.errors[0] }}</b-form-invalid-feedback>
        </b-form-group>
    </validation-provider>

</template>

<style scoped>

</style>
