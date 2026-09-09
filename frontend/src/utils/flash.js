const VALID_TYPES = ['success', 'error', 'warning', 'info']

export const parseFlashArgs = (arg1, arg2, arg3) => {
  let title = ''
  let message = ''
  let type = 'success'

  // Case 1: arg2 is a valid type (e.g. showFlash("message", "success", "BERHASIL"))
  if (typeof arg2 === 'string' && VALID_TYPES.includes(arg2.toLowerCase())) {
    type = arg2.toLowerCase()
    message = arg1 || ''
    title = arg3 || (type === 'error' ? 'GAGAL!' : type === 'warning' ? 'PERINGATAN!' : type === 'info' ? 'INFORMASI!' : 'BERHASIL!')
  }
  // Case 2: arg3 is a valid type (e.g. showFlash("TITLE", "message", "success"))
  else if (typeof arg3 === 'string' && VALID_TYPES.includes(arg3.toLowerCase())) {
    type = arg3.toLowerCase()
    title = arg1 || 'NOTIFICATION'
    message = arg2 || ''
  }
  // Case 3: fallback
  else {
    message = arg1 || ''
    type = (typeof arg2 === 'string' && VALID_TYPES.includes(arg2.toLowerCase())) ? arg2.toLowerCase() : 'success'
    title = arg3 || (type === 'error' ? 'GAGAL!' : type === 'warning' ? 'PERINGATAN!' : type === 'info' ? 'INFORMASI!' : 'BERHASIL!')
  }

  return { title, message, type }
}

export const showAdminFlash = (arg1, arg2, arg3) => {
  const { title, message, type } = parseFlashArgs(arg1, arg2, arg3)
  window.dispatchEvent(
    new CustomEvent('admin-flash', {
      detail: { title, message, type }
    })
  )
}

export const showHomeFlash = (arg1, arg2, arg3) => {
  const { title, message, type } = parseFlashArgs(arg1, arg2, arg3)
  window.dispatchEvent(
    new CustomEvent('show-flash', {
      detail: { title, message, type }
    })
  )
}

export const showFlash = (arg1, arg2, arg3) => {
  const isAdmin = window.location.pathname.startsWith('/admin')
  if (isAdmin) {
    showAdminFlash(arg1, arg2, arg3)
  } else {
    showHomeFlash(arg1, arg2, arg3)
  }
}

export default showFlash
