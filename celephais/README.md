### env files

+ `kadath/.env` <=> `celaphais/integrate/kadath.env` (see `kadath/.env.example`)
+ `ulthar/.env` <=> `celaphais/integrate/ulthar.env` (see `ulthar/.env.example`)

### 3rd party dependencies

+ recaptcha turing test <https://www.google.com/recaptcha/admin>
+ auth0 for underworld(postgrest) auth <https://auth0.com/>
  + you need to write `app_metadata` (Console => Users => User Detail) with `{"role": "PGSQL_DB_ROLE"}`
+ github oauth (however not used now)

### How to rock

0. make sure you can build kadath/ulthar/underworld (basicly composer & yarn)
1. terraform init
2. install <https://github.com/squat/terraform-provider-vultr> by hand
3. export VULTR_API_KEY=?
4. terraform apply

### What's not automated yet

+ postgreSQL create user/db/...
+ postgrest install (for underworld)
+ network
  + private ip config <https://www.vultr.com/docs/configuring-private-network> <https://coreos.com/os/docs/latest/network-config-with-networkd.html>
  + host bind

