import { reactive } from 'vue'

// We define the state OUTSIDE the function so it's shared globally
const alertState = reactive({
    show: false,
    message: '',
    type: 'success'
})

export function useAlert() {
    const showAlert = (message, type = 'success') => {
        alertState.message = message
        alertState.type = type
        alertState.show = true

        setTimeout(() => {
            alertState.show = false
        }, 4000)
    }

    return { alertState, showAlert }
}
