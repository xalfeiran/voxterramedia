import { useEffect, useRef, useState } from 'react'
import { Bot, ChevronDown, ChevronUp } from 'lucide-react'
import { useBotStore } from '@/stores/botStore'
import { BOTS, LOCATIONS, STATUSES, pickOther, randomInterval } from '@/lib/botData'

export default function BotStatusPanel() {
  const [expanded, setExpanded] = useState(false)
  const { bots, setJumping, land, activeBotId, setActiveBot } = useBotStore()
  const timersRef = useRef<ReturnType<typeof setTimeout>[]>([])

  // How many bots are currently jumping
  const jumpingCount = bots.filter(b => b.jumping).length

  useEffect(() => {
    const scheduleNext = (botId: number) => {
      timersRef.current[botId] = setTimeout(() => {
        setJumping(botId, true)

        setTimeout(() => {
          const currentLoc = useBotStore.getState().bots[botId].location
          const nextLoc    = pickOther(LOCATIONS, currentLoc)
          const nextStatus = STATUSES[Math.floor(Math.random() * STATUSES.length)]
          land(botId, nextLoc, nextStatus)
        }, 800)

        scheduleNext(botId)
      }, randomInterval())
    }

    BOTS.forEach((_, i) => {
      const initial = i * 4_000 + Math.random() * 2_000
      timersRef.current[i] = setTimeout(() => scheduleNext(i), initial)
    })

    return () => timersRef.current.forEach(clearTimeout)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  return (
    <div className="border-t border-slate-800">
      {/* ── Header / toggle row ── */}
      <button
        onClick={() => setExpanded(v => !v)}
        className="w-full flex items-center gap-2 px-4 py-3 hover:bg-slate-800/50 transition-colors text-left"
      >
        <Bot className="w-3.5 h-3.5 text-cyan-400 flex-none" />
        <span className="text-xs font-semibold text-slate-300 uppercase tracking-wider">
          AI Scout Activity
        </span>

        {/* Live pulse badge */}
        <span className="flex items-center gap-1 ml-1">
          <span className="relative flex h-1.5 w-1.5">
            <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-60" />
            <span className="relative inline-flex rounded-full h-1.5 w-1.5 bg-cyan-500" />
          </span>
        </span>

        {/* Collapsed summary */}
        {!expanded && (
          <span className="text-[10px] text-slate-500 ml-1">
            {jumpingCount > 0
              ? `${jumpingCount} jumping…`
              : `${BOTS.length} active`}
          </span>
        )}

        <span className="ml-auto text-slate-600">
          {expanded
            ? <ChevronUp className="w-3.5 h-3.5" />
            : <ChevronDown className="w-3.5 h-3.5" />}
        </span>
      </button>

      {/* ── Expanded content ── */}
      {expanded && (
        <div className="px-4 pb-4 space-y-1.5">
          {bots.map(bot => {
            const isActive = bot.id === activeBotId
            return (
              <button
                key={bot.id}
                onClick={() => setActiveBot(isActive ? null : bot.id)}
                className={`w-full text-left rounded-lg border p-2.5 transition-all duration-300 ${
                  isActive
                    ? 'border-slate-500 bg-slate-700/70 ring-1'
                    : bot.jumping
                    ? 'border-slate-600 bg-slate-700/40'
                    : 'border-slate-700/50 bg-slate-800/50 hover:border-slate-600 hover:bg-slate-800'
                }`}
                style={isActive ? { ringColor: bot.color } : {}}
              >
                <div className="flex items-center gap-2">
                  {/* Status dot */}
                  <span className="relative flex h-1.5 w-1.5 flex-none">
                    {!bot.jumping && (
                      <span
                        className="animate-ping absolute inline-flex h-full w-full rounded-full opacity-50"
                        style={{ backgroundColor: bot.color }}
                      />
                    )}
                    <span
                      className="relative inline-flex rounded-full h-1.5 w-1.5"
                      style={{ backgroundColor: bot.jumping ? '#94a3b8' : bot.color }}
                    />
                  </span>

                  <span
                    className="text-xs font-medium"
                    style={{ color: isActive ? bot.color : bot.jumping ? '#94a3b8' : bot.color }}
                  >
                    {bot.emoji} {bot.name}
                  </span>

                  {isActive && (
                    <span
                      className="ml-auto text-[9px] px-1.5 py-0.5 rounded-full border font-medium"
                      style={{ color: bot.color, borderColor: `${bot.color}55` }}
                    >
                      SELECTED
                    </span>
                  )}
                </div>

                {bot.jumping ? (
                  <p className="text-[10px] text-slate-500 mt-1 pl-3.5 animate-pulse">
                    ✈ jumping to next location…
                  </p>
                ) : (
                  <div className="mt-1 pl-3.5">
                    <p className="text-[10px] text-slate-400 leading-tight">
                      {bot.status} in{' '}
                      <span className="text-slate-300 font-medium">
                        {bot.location.flag} {bot.location.city}
                      </span>
                    </p>
                    <p className="text-[10px] text-slate-600 mt-0.5">
                      {bot.location.country}
                    </p>
                  </div>
                )}
              </button>
            )
          })}

          <p className="text-[10px] text-slate-600 text-center pt-1">
            {BOTS.length} scouts · {LOCATIONS.length} regions monitored
          </p>
        </div>
      )}
    </div>
  )
}
