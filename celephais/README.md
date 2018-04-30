### How to rock

0. make sure you can build kadath/ulthar/underworld (basicly composer & yarn)
1. terraform init
2. install <https://github.com/squat/terraform-provider-vultr> by hand
3. export VULTR_API_KEY=?
4. terraform apply

### What's not automated yet

+ postgreSQL create user/db/...
+ postgrest install
+ network
  + private ip config <https://www.vultr.com/docs/configuring-private-network> <https://coreos.com/os/docs/latest/network-config-with-networkd.html>
  + host bind

