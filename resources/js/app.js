import './bootstrap'
import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'

import { library } from '@fortawesome/fontawesome-svg-core'
import { 
  faPaperclip, faPaperPlane, faFile, faFileImage, faFileVideo, faFilePdf, faFileWord, faFileExcel, faFileZipper,
  faInbox, faBoxArchive // <-- BURADA EKLENDİ
} from '@fortawesome/free-solid-svg-icons'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

// Buraya ekledik
library.add(
  faPaperclip, faPaperPlane, faFile, faFileImage, faFileVideo, faFilePdf, faFileWord, faFileExcel, faFileZipper,
  faInbox, faBoxArchive
)

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })

    app.use(plugin)
    app.use(ZiggyVue)
    app.component('font-awesome-icon', FontAwesomeIcon)

    app.mount(el)
  },
  progress: { color: '#4B5563' },
})
