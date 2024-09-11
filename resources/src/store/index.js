import Vuex from "vuex";
import Vue from "vue";
import largeSidebar from "./modules/largeSidebar";
import compactSidebar from "./modules/compactSidebar";
import config from "./modules/config";
import auth from "./modules/auth";
import language from './modules/language';
import system from './modules/system';

Vue.use(Vuex);


// Create store
export default new Vuex.Store({
    modules: {
        language,
        auth,
        largeSidebar,
        compactSidebar,
        config,
        system
    }
});
