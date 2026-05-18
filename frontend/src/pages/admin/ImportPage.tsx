import { useState, useRef } from 'react'
import { Upload, FileText, CheckCircle, XCircle, AlertCircle, Download } from 'lucide-react'
import client from '@/api/client'

interface ImportRow {
  row:    number
  name:   string
  status: 'ok' | 'error'
  error?: string
}

interface ImportResult {
  imported: number
  errors:   number
  rows:     ImportRow[]
}

const CSV_TEMPLATE = `name,city_id,url,type,language,founded_year,description
El Ejemplo,1,https://example.com,newspaper,es,2020,A sample outlet
The Sample,42,https://sample.org,digital,en,2015,Another outlet`

export default function ImportPage() {
  const [file, setFile]         = useState<File | null>(null)
  const [loading, setLoading]   = useState(false)
  const [result, setResult]     = useState<ImportResult | null>(null)
  const [error, setError]       = useState('')
  const [preview, setPreview]   = useState<string[][]>([])
  const fileRef = useRef<HTMLInputElement>(null)

  const handleFile = (f: File) => {
    setFile(f)
    setResult(null)
    setError('')

    const reader = new FileReader()
    reader.onload = (e) => {
      const text = e.target?.result as string
      const rows = text.trim().split('\n').map(r => r.split(','))
      setPreview(rows.slice(0, 6)) // header + 5 rows
    }
    reader.readAsText(f)
  }

  const handleDrop = (e: React.DragEvent) => {
    e.preventDefault()
    const f = e.dataTransfer.files[0]
    if (f && (f.type === 'text/csv' || f.name.endsWith('.csv') || f.name.endsWith('.json'))) {
      handleFile(f)
    }
  }

  const handleImport = async () => {
    if (!file) return
    setLoading(true)
    setError('')
    try {
      const form = new FormData()
      form.append('file', file)
      const { data } = await client.post('/admin/media-outlets/bulk-import', form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
      setResult(data.data)
    } catch (err: unknown) {
      const msg = (err as { response?: { data?: { message?: string } } })
        ?.response?.data?.message ?? 'Import failed'
      setError(msg)
    } finally {
      setLoading(false)
    }
  }

  const downloadTemplate = () => {
    const blob = new Blob([CSV_TEMPLATE], { type: 'text/csv' })
    const url  = URL.createObjectURL(blob)
    const a    = document.createElement('a')
    a.href = url; a.download = 'voxterra-import-template.csv'; a.click()
    URL.revokeObjectURL(url)
  }

  return (
    <div className="p-8 max-w-3xl">
      <div className="mb-6 flex items-start justify-between">
        <div>
          <h1 className="text-2xl font-bold text-white">Bulk Import</h1>
          <p className="text-sm text-slate-400 mt-1">Upload a CSV or JSON file to import multiple outlets at once.</p>
        </div>
        <button
          onClick={downloadTemplate}
          className="flex items-center gap-1.5 text-sm text-blue-400 hover:text-blue-300 transition-colors"
        >
          <Download className="w-4 h-4" />
          CSV template
        </button>
      </div>

      {/* Format hint */}
      <div className="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-6">
        <h2 className="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-2 flex items-center gap-1.5">
          <FileText className="w-3.5 h-3.5" />Required CSV columns
        </h2>
        <div className="flex flex-wrap gap-2">
          {['name *','city_id *','url *','type *','language *','founded_year','description','logo_url'].map(col => (
            <span key={col} className={`text-xs px-2 py-0.5 rounded font-mono ${
              col.includes('*') ? 'bg-blue-500/10 text-blue-300' : 'bg-slate-800 text-slate-400'
            }`}>{col}</span>
          ))}
        </div>
        <p className="text-xs text-slate-500 mt-2">type: national | newspaper | digital | tv | radio | magazine</p>
        <p className="text-xs text-slate-500">language: en | es | fr</p>
      </div>

      {/* Drop zone */}
      <div
        onDrop={handleDrop}
        onDragOver={e => e.preventDefault()}
        onClick={() => fileRef.current?.click()}
        className={`border-2 border-dashed rounded-xl p-10 text-center cursor-pointer transition-colors ${
          file ? 'border-blue-500/50 bg-blue-500/5' : 'border-slate-700 hover:border-slate-600 hover:bg-slate-900/50'
        }`}
      >
        <input
          ref={fileRef}
          type="file"
          accept=".csv,.json"
          className="hidden"
          onChange={e => e.target.files?.[0] && handleFile(e.target.files[0])}
        />
        {file ? (
          <>
            <FileText className="w-8 h-8 text-blue-400 mx-auto mb-2" />
            <p className="font-medium text-white">{file.name}</p>
            <p className="text-xs text-slate-400 mt-1">{(file.size / 1024).toFixed(1)} KB</p>
          </>
        ) : (
          <>
            <Upload className="w-8 h-8 text-slate-500 mx-auto mb-2" />
            <p className="text-sm text-slate-400">Drag & drop a CSV or JSON file, or click to browse</p>
          </>
        )}
      </div>

      {/* Preview */}
      {preview.length > 0 && (
        <div className="mt-4 bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
          <p className="text-xs text-slate-500 px-4 py-2 border-b border-slate-800">Preview (first 5 rows)</p>
          <div className="overflow-x-auto">
            <table className="w-full text-xs">
              <thead>
                <tr className="border-b border-slate-800">
                  {(preview[0] ?? []).map((col, i) => (
                    <th key={i} className="text-left px-3 py-2 text-slate-400 font-medium">{col}</th>
                  ))}
                </tr>
              </thead>
              <tbody>
                {preview.slice(1).map((row, i) => (
                  <tr key={i} className="border-b border-slate-800/50">
                    {row.map((cell, j) => (
                      <td key={j} className="px-3 py-1.5 text-slate-300">{cell}</td>
                    ))}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      )}

      {/* Error */}
      {error && (
        <div className="mt-4 flex items-center gap-2 bg-red-500/10 border border-red-500/30 text-red-400 text-sm rounded-lg px-4 py-3">
          <AlertCircle className="w-4 h-4 flex-none" />{error}
        </div>
      )}

      {/* Result */}
      {result && (
        <div className="mt-4 bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-4">
          <div className="flex items-center gap-6">
            <div className="flex items-center gap-2 text-emerald-400">
              <CheckCircle className="w-5 h-5" />
              <span className="font-semibold">{result.imported} imported</span>
            </div>
            {result.errors > 0 && (
              <div className="flex items-center gap-2 text-red-400">
                <XCircle className="w-5 h-5" />
                <span className="font-semibold">{result.errors} errors</span>
              </div>
            )}
          </div>
          {result.rows.filter(r => r.status === 'error').map(row => (
            <div key={row.row} className="flex items-start gap-2 text-xs text-red-300">
              <XCircle className="w-3.5 h-3.5 mt-0.5 flex-none" />
              <span>Row {row.row}: <strong>{row.name}</strong> — {row.error}</span>
            </div>
          ))}
        </div>
      )}

      {/* Import button */}
      <div className="mt-6">
        <button
          onClick={handleImport}
          disabled={!file || loading}
          className="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 disabled:opacity-40 disabled:cursor-not-allowed text-white font-medium text-sm px-6 py-3 rounded-lg transition-colors"
        >
          <Upload className="w-4 h-4" />
          {loading ? 'Importing…' : 'Run import'}
        </button>
      </div>
    </div>
  )
}
