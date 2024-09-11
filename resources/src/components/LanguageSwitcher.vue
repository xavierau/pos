<script>
import {mapGetters} from "vuex";

export default {
    name: "LanguageSwitcher",
    computed: {
        ...mapGetters(['supportedLanguages'])
    },
    methods: {
        setLocal(locale) {
            this.$i18n.locale = locale;
            this.$store.dispatch("language/setLanguage", locale);
            Fire.$emit("ChangeLanguage");
        },
    }
}
</script>

<template>
    <div class="dropdown">
        <b-dropdown
            id="dropdown"
            text="Dropdown Button"
            class="m-md-2"
            toggle-class="text-decoration-none"
            no-caret
            right
            variant="link"
        >
            <template slot="button-content">
                <i
                    class="i-Globe text-muted header-icon"
                    role="button"
                    id="dropdownMenuButton"
                    data-toggle="dropdown"
                    aria-haspopup="true"
                    aria-expanded="false"
                ></i>
            </template>
            <vue-perfect-scrollbar
                :settings="{ suppressScrollX: true, wheelPropagation: false }"
                ref="myData"
                class="dropdown-menu-left rtl-ps-none notification-dropdown ps scroll"
            >
                <div class="menu-icon-grid">
                    <a v-for="lang in supportedLanguages"
                       :key="lang.code"
                       @click="setLocal(lang.code)">
                        <i title="en" class="flag-icon flag-icon-squared" :class="lang.icon"></i>
                        {{ lang.label }}
                    </a>
                </div>
            </vue-perfect-scrollbar>
        </b-dropdown>
    </div>

</template>

<style scoped>

</style>
