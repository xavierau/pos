import Vue from 'vue'
import Vuex from 'vuex'
import axios from 'axios'
import store from '../../store/index.js'
import {i18n} from "../../plugins/i18n";


Vue.use(Vuex)


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
    setLoading(state, data) {
        state.loading = data;
        state.error = null;
    },
    setError(state, data) {
        state.error = data;
        state.loggedInUser = null;
        state.loading = false;
    },
    clearError(state) {
        state.error = null;
    },

    setPermissions(state, Permissions) {
        state.Permissions = Permissions;
    },

    setAllModules(state, allmodules) {
        state.allmodules = allmodules;
    },


    setUser(state, user) {
        state.user = user;
    },


    SetDefaultLanguage(state, Language) {
        i18n.locale = Language;
        store.dispatch("language/setLanguage", Language);
        state.Default_Language = Language;
    },

    Notifs_alert(state, notifs) {
        state.notifs = notifs;
    },


    logout(state) {
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

        await axios.get("get_user_auth").then((userAuth) => {
            let Permissions = userAuth.data.permissions
            let allmodules = userAuth.data.ModulesEnabled
            let user = userAuth.data.user
            let notifs = userAuth.data.notifs
            let default_language = userAuth.data.user.default_language

            context.commit('setPermissions', Permissions)
            context.commit('setAllModules', allmodules)
            context.commit('setUser', user)
            context.commit('Notifs_alert', notifs)

            context.commit('SetDefaultLanguage', default_language)
        }).catch(() => {
            context.commit('setPermissions', null)
            context.commit('setAllModules', null)
            context.commit('setAllModules', null)
            context.commit('setUser', null)
            context.commit('Notifs_alert', null)
            context.commit('SetDefaultLanguage', 'en')
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
