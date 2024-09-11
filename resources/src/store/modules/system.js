const state = {
    languages: [
        {label: "English", code: "en", icon: "flag-icon-gb"},
        // {label: "French", code: "fr", icon: "flag-icon-fr"},
        // {label: "Arabic", code: "ar", icon: "flag-icon-sa"},
        // {label: "Turkish", code: "tur", icon: "flag-icon-tr"},
        {label: "Simplified Chinese", code: "sm_ch", icon: "flag-icon-cn"},
        // {label: "Thaï", code: "thai", icon: "flag-icon-th"},
        // {label: "Hindi", code: "hn", icon: "flag-icon-in"},
        // {label: "German", code: "de", icon: "flag-icon-de"},
        // {label: "Spanish", code: "es", icon: "flag-icon-es"},
        // {label: "Italian", code: "it", icon: "flag-icon-it"},
        // {label: "Indonesian", code: "Ind", icon: "flag-icon-id"},
        {label: "Traditional Chinese", code: "tr_ch", icon: "flag-icon-tw"},
        // {label: "Russian", code: "ru", icon: "flag-icon-ru"},
        // {label: "Vietnamese", code: "vn", icon: "flag-icon-vn"},
        // {label: "Korean", code: "kr", icon: "flag-icon-kr"},
        // {label: "Bangla", code: "ba", icon: "flag-icon-bd"},
        // {label: "Portuguese", code: "br", icon: "flag-icon-pt"},
    ]
};

const getters = {

    supportedLanguages: (state) => state.languages,
};

const actions = {};

const mutations = {};

export default {
    state,
    getters,
    actions,
    mutations,
};
