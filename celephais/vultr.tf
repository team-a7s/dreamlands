provider "vultr" {
  
}

// Find the ID of the Silicon Valley region.
data "vultr_region" "default" {
  filter {
    name   = "name"
    values = ["Los Angeles"]
  }
}

// Find the ID for centos
data "vultr_os" "centos" {
  filter {
    name   = "name"
    values = ["CentOS 7 x64"]
  }
}


// Find the ID for CoreOS Container Linux.
data "vultr_os" "coreos" {
  filter {
    name   = "name"
    values = ["CoreOS Stable"]
  }
}

// Find the ID for a starter plan.
data "vultr_plan" "starter" {
  filter {
    name   = "price_per_month"
    values = ["5.00"]
  }

  filter {
    name   = "ram"
    values = ["1024"]
  }
}

// Find the ID for a starter plan.
data "vultr_plan" "1c2g" {
  filter {
    name   = "price_per_month"
    values = ["10.00"]
  }

  filter {
    name   = "ram"
    values = ["2048"]
  }
}


// Find the ID of an existing SSH key.
data "vultr_ssh_key" "a7s" {
  filter {
    name   = "name"
    values = ["a7s"]
  }
}


//pack the repo
data "external" "pack" {
  program = ["bash", "${path.module}/integrate/pack.sh"]

  query = {
  }
}

// Create a new firewall group.
resource "vultr_firewall_group" "dreamlands" {
  description = "dreamlands firewall"
}

resource "vultr_firewall_rule" "ssh" {
  firewall_group_id = "${vultr_firewall_group.dreamlands.id}"
  cidr_block        = "0.0.0.0/0"
  protocol          = "tcp"
  from_port         = 22
  to_port           = 22
}

resource "vultr_firewall_rule" "http" {
  firewall_group_id = "${vultr_firewall_group.dreamlands.id}"
  cidr_block        = "0.0.0.0/0"
  protocol          = "tcp"
  from_port         = 80
  to_port           = 80
}

resource "vultr_firewall_rule" "https" {
  firewall_group_id = "${vultr_firewall_group.dreamlands.id}"
  cidr_block        = "0.0.0.0/0"
  protocol          = "tcp"
  from_port         = 443
  to_port           = 443
}

resource "vultr_firewall_rule" "mosh" {
  firewall_group_id = "${vultr_firewall_group.dreamlands.id}"
  cidr_block        = "0.0.0.0/0"
  protocol          = "udp"
  from_port         = 60000
  to_port           = 61000
}

// Create a Vultr virtual machine.
resource "vultr_instance" "db-a7s" {
  name              = "db-a7s"
  region_id         = "${data.vultr_region.default.id}"
  plan_id           = "${data.vultr_plan.1c2g.id}"
  os_id             = "${data.vultr_os.centos.id}"
  ssh_key_ids       = ["${data.vultr_ssh_key.a7s.id}"]
  hostname          = "db.a7s"
  tag               = "database"
  firewall_group_id = "${vultr_firewall_group.dreamlands.id}"
  private_networking = true

  provisioner "remote-exec" {
    inline = [
      "yum install -y https://download.postgresql.org/pub/repos/yum/10/redhat/rhel-7-x86_64/pgdg-centos10-10-2.noarch.rpm",
      "yum update -y",
      "yum install -y epel-release",
      "yum install -y mosh postgresql10-server",
      "/usr/pgsql-10/bin/postgresql-10-setup initdb",
      "systemctl enable postgresql-10",
      "systemctl start postgresql-10",
      "firewall-cmd --permanent --add-service=postgresql",
      "firewall-cmd --permanent --add-service=mosh",
      "firewall-cmd --reload"
    ]
  }
}

resource "vultr_instance" "app-a7s" {
  name              = "app-a7s"
  region_id         = "${data.vultr_region.default.id}"
  plan_id           = "${data.vultr_plan.starter.id}"
  os_id             = "${data.vultr_os.coreos.id}"
  ssh_key_ids       = ["${data.vultr_ssh_key.a7s.id}"]
  hostname          = "app.a7s"
  tag               = "database"
  firewall_group_id = "${vultr_firewall_group.dreamlands.id}"
  private_networking = true

  provisioner "file" {
    source      = "../dreamlands.tar.gz"
    destination = "/tmp/dreamlands.tgz"
  }
  provisioner "remote-exec" {
    inline = [
      "echo 'DNS=1.1.1.1' >> /etc/systemd/resolved.conf",
      "systemctl restart systemd-resolved",
      <<EOF
export DOCKER_COMPOSE_VERSION=`git ls-remote --tags git://github.com/docker/compose.git | awk '{print $2}' |grep -v "docs\|rc" |awk -F'/' '{print $3}' |sort -V |tail -n1`
EOF
      ,
      "mkdir -p /opt/bin",
      "curl -L https://github.com/docker/compose/releases/download/$DOCKER_COMPOSE_VERSION/docker-compose-`uname -s`-`uname -m` > /opt/bin/docker-compose",
      "chmod +x /opt/bin/docker-compose",
      "mkdir /srv/dreamlands",
      "tar xf /tmp/dreamlands.tgz -C /srv/dreamlands",
      "/srv/dreamlands/celephais/integrate/build.sh"
    ]
  }
}


