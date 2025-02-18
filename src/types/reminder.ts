export type ReminderType = 'appointment' | 'task' | 'event' | 'meeting' | 'call'
export type ReminderStatus = 'scheduled' | 'sent' | 'failed'

export interface Reminder {
  id: string
  user_id: string
  title: string
  type: ReminderType
  date: string
  time: string
  timezone: string
  recipients: string[]
  status: ReminderStatus
  created_at: string
}
