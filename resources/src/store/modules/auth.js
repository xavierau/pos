import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import store from '../../store/index.js'
import {i18n} from "../../plugins/i18n";


Vue.use(Vuex)

export const authMutations = {
    setLoading: 'auth/setLoading',
    setError: 'auth/setError',
    setUser: 'auth/setUser',
    clearError: 'auth/clearError',
    setPermissions: 'auth/setPermissions',
    setAllModules: 'auth/setAllModules',
    setDefaultLanguage: 'auth/SetDefaultLanguage',
    alert: 'auth/alert',
    logout: 'auth/logout',
}

const state = {
    isAuthenticated: false,
    Permissions: null,
    allmodules: null,
    user: {},
    loading: false,
    error: null,
    notifs: 0,
    Default_Language: 'en',
};

const getters = {
    getAllModules: state => state.allmodules,
    isAuthenticated: state => state.isAuthenticated,
    currentUser: state => state.user,
    currentUserPermissions: state => state.Permissions,
    loading: state => state.loading,
    notifs_alert: state => state.notifs,
    DefaultLanguage: state => state.Default_Language,
    error: state => state.error,
    hasPermission: (state) => (permissions) => {

        if (!state.Permissions) {
            return false;
        }

        if (Array.isArray(permissions)) {

            for (let i = 0; i < permissions.length; i++) {
                if (state.Permissions.includes(permissions[i])) {
                    return true;
                }
            }
            return false;
        } else {
            return state.Permissions.includes(permissions);
        }
    }
};

const mutations = {
    [authMutations.setLoading](state, data) {
        state.loading = data;
        state.error = null;
    },
    [authMutations.setError](state, data) {
        state.error = data;
        state.loggedInUser = null;
        state.loading = false;
    },
    [authMutations.clearError](state) {
        state.error = null;
    },
    [authMutations.setPermissions](state, Permissions) {
        state.Permissions = Permissions;
    },
    [authMutations.setAllModules](state, allmodules) {
        state.allmodules = allmodules;
    },
    [authMutations.setUser](state, user) {
        state.user = user;
    },
    [authMutations.setDefaultLanguage](state, Language) {
        i18n.locale = Language;
        store.dispatch("language/setLanguage", Language);
        state.Default_Language = Language;
    },
    [authMutations.alert](state, notifs) {
        state.notifs = notifs;
    },
    [authMutations.logout](state) {
        state.user = null;
        state.Permissions = null;
        state.allmodules = null;
        state.loggedInUser = null;
        state.loading = false;
        state.error = null;
    },
};

const actions = {

    async refreshUserPermissions(context) {

        await axios.get("/api/get_user_auth").then((userAuth) => {
            let Permissions = userAuth.data.permissions
            let allmodules = userAuth.data.ModulesEnabled
            let user = userAuth.data.user
            let notifs = userAuth.data.notifs
            let default_language = userAuth.data.user.default_language

            console.log("Going to set user", user)
            context.commit(authMutations.setPermissions, Permissions)
            context.commit(authMutations.setAllModules, allmodules)
            context.commit(authMutations.setUser, user)
            context.commit(authMutations.alert, notifs)
            context.commit(authMutations.setDefaultLanguage, default_language)
        }).catch((e) => {
            console.error('get_user_auth error', e)
            context.commit(authMutations.setPermissions, null)
            context.commit(authMutations.setAllModules, null)
            context.commit(authMutations.setAllModules, null)
            context.commit(authMutations.setUser, null)
            context.commit(authMutations.alert, null)
            context.commit(authMutations.setDefaultLanguage, 'en')
        });
    },

    logout({commit}) {

        axios({method: 'post', url: '/logout', baseURL: ''})
            .then((userData) => {
                window.location.href = '/login';
            })
    },
};

export default {
    state,
    getters,
    actions,
    mutations
};
