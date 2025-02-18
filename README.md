Reminders WebApp sends reminder notifications of upcoming events, appointments, tasks, calls and meetings.
Businesses can use it to remind their customers of important events with email notifications which are send out on a schedule.

Sign up for a hosted SaaS cloud version at https://reminders.pro

Technical setup:
- Created with bolt.diy and AI Claude 3.5 Sonnet
- Backend: Create Supabase project and enter API key on .env file
- Frontend: Vue template from codacus. To create static files to host anywhere (no nodejs needed) compile the build at Bolt Terminal with command "npm run build" -> upload the files in dist-folder to webroot.
- Signup email smtp setup in supabase for sendgrid
- PHP file send_reminders.php scheduled with cron job at hosting site (or create supabase edge function to send on a schedule). Supabase provides another API key without row level security.

  Supabase table definition:
  create table public.reminders (
  id uuid not null default extensions.uuid_generate_v4 (),
  user_id uuid not null,
  title text not null,
  type text not null,
  date date not null,
  time time without time zone not null,
  timezone text not null,
  recipients text[] not null,
  created_at timestamp with time zone not null default timezone ('utc'::text, now()),
  status text not null default 'scheduled'::text,
  constraint reminders_pkey primary key (id),
  constraint reminders_user_id_fkey foreign KEY (user_id) references auth.users (id),
  constraint reminders_status_check check (
    (
      status = any (
        array['scheduled'::text, 'sent'::text, 'failed'::text]
      )
    )
  )
) TABLESPACE pg_default;
