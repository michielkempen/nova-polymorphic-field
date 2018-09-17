Nova.booting((Vue, router) => {
    Vue.component('index-polymorphic-field', require('./components/IndexField'));
    Vue.component('detail-polymorphic-field', require('./components/DetailField'));
    Vue.component('form-polymorphic-field', require('./components/FormField'));
})
