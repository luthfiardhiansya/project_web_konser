export const showFlash = (titleOrMessage, message, type = 'success') => {
  let title = 'NOTIFICATION'
  let msg = ''

  if (message === undefined) {
    msg = titleOrMessage || ''
    title = type === 'error' ? 'GAGAL!' : type === 'warning' ? 'PERINGATAN!' : type === 'info' ? 'INFORMASI!' : 'BERHASIL!'
  } else {
    title = titleOrMessage || 'NOTIFICATION'
    msg = message || ''
  }

  window.dispatchEvent(
    new CustomEvent('show-flash', {
      detail: {
        title,
        message: msg,
        type
      }
    })
  )
}

export default showFlash
