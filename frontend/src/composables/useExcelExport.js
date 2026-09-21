/**
 * useExcelExport — kirim data halaman aktif ke backend untuk diexport sebagai .xlsx
 *
 * Cara pakai di view:
 *   import { useExcelExport } from '../../composables/useExcelExport'
 *   const { exportExcel, exporting } = useExcelExport()
 *
 *   exportExcel({
 *     title   : 'Data Kategori',
 *     filename: 'kategori',
 *     columns : ['ID', 'Nama', 'Deskripsi'],
 *     rows    : paginatedItems.value.map(k => [k.id, k.nama_kategori, k.deskripsi || '-']),
 *   })
 */

import { ref } from 'vue'
import api from '../utils/api'
import { showFlash } from '../utils/flash'

export function useExcelExport() {
  const exporting = ref(false)

  const exportExcel = async ({ title, filename, columns, rows }) => {
    if (exporting.value) return
    exporting.value = true
    try {
      const res = await api.post(
        '/reports/export/excel-page',
        { title, columns, rows, filename },
        { responseType: 'blob' }
      )

      // Buat link download dari blob yang dikembalikan backend
      const blob = new Blob([res.data], {
        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      })
      const url  = URL.createObjectURL(blob)
      const link = document.createElement('a')
      link.href     = url
      link.download = (filename || 'export') + '_hal' + '.xlsx'
      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)
      URL.revokeObjectURL(url)

      showFlash(`Export Excel berhasil! (${rows.length} baris)`, 'success', 'EXPORT BERHASIL')
    } catch (err) {
      console.error('Export gagal:', err)
      showFlash(err.response?.data?.message || 'Gagal mengekspor data.', 'error', 'EXPORT GAGAL')
    } finally {
      exporting.value = false
    }
  }

  return { exportExcel, exporting }
}
