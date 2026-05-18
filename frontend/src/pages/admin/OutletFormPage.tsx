import { useEffect } from 'react'
import { useNavigate, useParams, Link } from 'react-router-dom'
import { useForm } from 'react-hook-form'
import { zodResolver } from '@hookform/resolvers/zod'
import { z } from 'zod'
import { ChevronLeft, Save, AlertCircle } from 'lucide-react'
import { useMediaOutlet } from '@/api/queries'
import { useCreateOutlet, useUpdateOutlet } from '@/api/mutations'

const emptyToUndefined = (v: unknown) => (v === '' || v === null ? undefined : v)

const schema = z.object({
  name:         z.string().min(2, 'Name is required'),
  city_id:      z.coerce.number().positive('City is required'),
  url:          z.string().url('Must be a valid URL'),
  type:         z.enum(['national','newspaper','digital','tv','radio','magazine']),
  language:     z.enum(['en','es','fr','de','pt','ar','zh','ja','ko','ru','hi','it','nl','pl','sv','tr','fa','he','id','ms','th','vi','uk','ro','hu']),
  description:  z.string().optional(),
  logo_url:     z.preprocess(emptyToUndefined, z.string().url('Must be a valid URL').optional()),
  founded_year: z.preprocess(emptyToUndefined, z.coerce.number().min(1600).max(2100).optional()),
  is_active:    z.boolean().default(true),
  is_featured:  z.boolean().default(false),
  latitude:     z.preprocess(emptyToUndefined, z.coerce.number().min(-90).max(90).optional()),
  longitude:    z.preprocess(emptyToUndefined, z.coerce.number().min(-180).max(180).optional()),
})

type FormData = z.infer<typeof schema>

function FieldError({ message }: { message?: string }) {
  if (!message) return null
  return <p className="text-xs text-red-400 mt-1 flex items-center gap-1"><AlertCircle className="w-3 h-3" />{message}</p>
}

function Label({ children }: { children: React.ReactNode }) {
  return <label className="block text-xs font-medium text-slate-400 mb-1.5">{children}</label>
}

function Input({ className = '', ...props }: React.InputHTMLAttributes<HTMLInputElement>) {
  return (
    <input
      className={`w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors ${className}`}
      {...props}
    />
  )
}

function Select({ className = '', children, ...props }: React.SelectHTMLAttributes<HTMLSelectElement>) {
  return (
    <select
      className={`w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-white focus:outline-none focus:border-blue-500 transition-colors ${className}`}
      {...props}
    >
      {children}
    </select>
  )
}

export default function OutletFormPage() {
  const { id } = useParams<{ id: string }>()
  const isEdit = !!id
  const navigate = useNavigate()

  const { data: outlet } = useMediaOutlet(isEdit ? id ?? '' : '')
  const create = useCreateOutlet()
  const update = useUpdateOutlet()

  const { register, handleSubmit, reset, formState: { errors, isSubmitting } } = useForm<FormData>({
    resolver: zodResolver(schema),
    defaultValues: { is_active: true, is_featured: false, type: 'newspaper', language: 'en' },
  })

  useEffect(() => {
    if (outlet) {
      reset({
        name:         outlet.name,
        city_id:      outlet.city.id,
        url:          outlet.url,
        type:         outlet.type,
        language:     outlet.language,
        description:  outlet.description ?? '',
        logo_url:     outlet.logo_url,
        founded_year: outlet.founded_year,
        is_active:    outlet.is_active,
        is_featured:  outlet.is_featured,
        latitude:     outlet.latitude,
        longitude:    outlet.longitude,
      })
    }
  }, [outlet, reset])

  const onSubmit = async (data: FormData) => {
    try {
      if (isEdit && id) {
        await update.mutateAsync({ id: parseInt(id), data })
      } else {
        await create.mutateAsync(data)
      }
      navigate('/admin/outlets')
    } catch (err) {
      console.error(err)
    }
  }

  return (
    <div className="p-8 max-w-3xl">
      {/* Breadcrumb */}
      <div className="flex items-center gap-2 mb-6 text-sm text-slate-400">
        <Link to="/admin/outlets" className="flex items-center gap-1 hover:text-white transition-colors">
          <ChevronLeft className="w-4 h-4" />Outlets
        </Link>
        <span>/</span>
        <span className="text-white">{isEdit ? 'Edit outlet' : 'New outlet'}</span>
      </div>

      <h1 className="text-2xl font-bold text-white mb-6">{isEdit ? 'Edit outlet' : 'New outlet'}</h1>

      <form onSubmit={handleSubmit(onSubmit)} className="space-y-6">
        {/* Core info */}
        <div className="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
          <h2 className="text-sm font-semibold text-slate-300 mb-2">Core information</h2>

          <div>
            <Label>Name *</Label>
            <Input {...register('name')} placeholder="The New York Times" />
            <FieldError message={errors.name?.message} />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label>Type *</Label>
              <Select {...register('type')}>
                <option value="national">National</option>
                <option value="newspaper">Newspaper</option>
                <option value="digital">Digital</option>
                <option value="tv">TV</option>
                <option value="radio">Radio</option>
                <option value="magazine">Magazine</option>
              </Select>
              <FieldError message={errors.type?.message} />
            </div>
            <div>
              <Label>Language *</Label>
              <Select {...register('language')}>
                <option value="en">English</option>
                <option value="es">Spanish</option>
                <option value="fr">French</option>
                <option value="de">German</option>
                <option value="pt">Portuguese</option>
                <option value="ar">Arabic</option>
                <option value="zh">Chinese</option>
                <option value="ja">Japanese</option>
                <option value="ko">Korean</option>
                <option value="ru">Russian</option>
                <option value="hi">Hindi</option>
                <option value="it">Italian</option>
                <option value="nl">Dutch</option>
                <option value="pl">Polish</option>
                <option value="sv">Swedish</option>
                <option value="tr">Turkish</option>
                <option value="fa">Persian</option>
                <option value="he">Hebrew</option>
                <option value="id">Indonesian</option>
                <option value="ms">Malay</option>
                <option value="th">Thai</option>
                <option value="vi">Vietnamese</option>
                <option value="uk">Ukrainian</option>
                <option value="ro">Romanian</option>
                <option value="hu">Hungarian</option>
              </Select>
              <FieldError message={errors.language?.message} />
            </div>
          </div>

          <div>
            <Label>Website URL *</Label>
            <Input {...register('url')} type="url" placeholder="https://www.example.com" />
            <FieldError message={errors.url?.message} />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label>City ID *</Label>
              <Input {...register('city_id')} type="number" placeholder="City database ID" />
              <FieldError message={errors.city_id?.message} />
            </div>
            <div>
              <Label>Founded year</Label>
              <Input {...register('founded_year')} type="number" placeholder="1851" />
              <FieldError message={errors.founded_year?.message} />
            </div>
          </div>

          <div>
            <Label>Description</Label>
            <textarea
              {...register('description')}
              rows={3}
              placeholder="Brief description of the outlet…"
              className="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 transition-colors resize-none"
            />
          </div>
        </div>

        {/* Optional fields */}
        <div className="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-4">
          <h2 className="text-sm font-semibold text-slate-300 mb-2">Optional</h2>

          <div>
            <Label>Logo URL</Label>
            <Input {...register('logo_url')} type="url" placeholder="https://example.com/logo.png" />
            <FieldError message={errors.logo_url?.message} />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label>Latitude override</Label>
              <Input {...register('latitude')} type="number" step="any" placeholder="40.7128" />
              <FieldError message={errors.latitude?.message} />
            </div>
            <div>
              <Label>Longitude override</Label>
              <Input {...register('longitude')} type="number" step="any" placeholder="-74.0060" />
              <FieldError message={errors.longitude?.message} />
            </div>
          </div>

          <div className="flex items-center gap-6 pt-1">
            <label className="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" {...register('is_active')} className="w-4 h-4 accent-blue-500" />
              <span className="text-sm text-slate-300">Active</span>
            </label>
            <label className="flex items-center gap-2 cursor-pointer">
              <input type="checkbox" {...register('is_featured')} className="w-4 h-4 accent-amber-400" />
              <span className="text-sm text-slate-300">Featured</span>
            </label>
          </div>
        </div>

        {/* Submit */}
        <div className="flex items-center justify-end gap-3">
          <Link
            to="/admin/outlets"
            className="px-4 py-2 text-sm text-slate-400 hover:text-white transition-colors"
          >
            Cancel
          </Link>
          <button
            type="submit"
            disabled={isSubmitting}
            className="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 disabled:opacity-50 text-white font-medium text-sm px-5 py-2.5 rounded-lg transition-colors"
          >
            <Save className="w-4 h-4" />
            {isSubmitting ? 'Saving…' : isEdit ? 'Save changes' : 'Create outlet'}
          </button>
        </div>
      </form>
    </div>
  )
}
