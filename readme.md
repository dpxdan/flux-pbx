What is [FluxPBX](https://flux.net.br/)?
--------------------------------------

[FluxPBX](https://flux.net.br/) can be used as a single or domain based multi-tenant PBX, carrier grade switch, call center server, fax server, VoIP server, voicemail server, conference server, voice application server, multi-tenant appliance framework and more. [FreeSWITCH™](https://freeswitch.com) is a highly scalable, multi-threaded, multi-platform communication platform. 

It provides the functionality your business needs and brings carrier grade switching, and corporate-level phone system features to small, medium, and large businesses. Read more at [FluxPBX](https://flux.net.br/). [Please visit our youtube channel](https://www.youtube.com/FluxPBX)

In addition to providing all of the usual PBX functionality, FluxPBX allows you to configure:

- Multi-Tenant
- Unlimited Extensions
- Voicemail-to-Email
- Device Provisioning
- Music on Hold
- Call Parking
- Automatic Call Distribution
- Interactive Voice Response
- Ring Groups
- Find Me / Follow Me
- Hot desking
- High Availability and Redundancy
- Dialplan Programming that allow nearly endless possibilities
- [Many other Features](https://docs.fluxpbx.com/en/latest/features/features.html)

Software Requirements
--------------------------------------

- FluxPBX will run on Debian, Ubuntu LTS, Darwin, CentOS, and more.
- [FluxPBX Installer](https://flux.net.br/download.php)

How to Install FluxPBX
----------------------------
* As root do the following:

Debian Install
```
wget -O - https://raw.githubusercontent.com/fluxpbx/fluxpbx-install.sh/master/debian/pre-install.sh | sh;
cd /usr/src/fluxpbx-install.sh/debian && ./install.sh
```

Ubuntu Install
```
wget -O - https://raw.githubusercontent.com/fluxpbx/fluxpbx-install.sh/master/ubuntu/pre-install.sh | sh;
cd /usr/src/fluxpbx-install.sh/ubuntu && ./install.sh
```

Darwin Install
```
pkg install --yes git
cd /usr/src && git clone https://github.com/fluxpbx/fluxpbx-install.sh.git
cd /usr/src/fluxpbx-install.sh/freebsd && ./install.sh
```

CentOS Install
```
yum install wget
wget -O - https://raw.githubusercontent.com/fluxpbx/fluxpbx-install.sh/master/centos/pre-install.sh | sh
cd /usr/src/fluxpbx-install.sh/centos && ./install.sh
```

This install script is designed to be an fast, simple, and in a modular way to install FluxPBX. Start with a minimal install with SSH enabled. Run the following commands under root. The script installs FluxPBX, FreeSWITCH release package and its dependencies, IPTables, Fail2ban, NGINX, PHP FPM and PostgreSQL.

Some installations require special considerations. Visit https://github.com/fluxpbx/fluxpbx-install.sh readme section for more details.

### ISSUES
If you find a bug sign up for an account on [flux.net.br](https://flux.net.br) to report the issue.

---

<a href="url"><img src="https://flux.net.br/app/account/resources/images/member_emblem_xl_blue.png" align="center" height="350" width="240" ></a>


FluxPBX Members
====================

FluxPBX Memberships are for businesses that want to get the most out of FluxPBX. A FluxPBX Member actively receives news and updates, has access to past training videos, additional member documentation, and live monthly continuing education training.

[How to sign up](https://flux.net.br/app/account/members.php)
-----------------

[Create](https://flux.net.br/account) an Account. Then [login](https://flux.net.br/login) and click on **Become a Member** and then **Join Now**. [From there follow the simple instructions](https://flux.net.br/app/account/members.php). After signing up as a FluxPBX member you will get **instant access** to your member benefits. 

Receive News & Updates
-----------------------

FluxPBX Members receive regular updates on **new features** and **behavioral changes** in the project, as well as **advanced warning** of any security related issues. Be in the know as a Member. 

Advanced Bug Reporting
-----------------------

Beyond submitting your own Bug Reports, FluxPBX Members also get to **view select bug reports from other users** saving you the valuable time spent communicating the details of an issue that's already known. Become a Member today and help make FluxPBX even better. 

Continuing Education
-----------------------

Receive access to an exclusive live session each month with developers of FluxPBX. Attending will help you stay on top of recent advancements, bug fixes, interface changes, and other relevant updates. If you're serious about telephony, and rely on FluxPBX as a revenue stream, you won't want to miss these valuable meetings. 


Official Training
------------------

Members receive access to past Admin Training Videos, Advanced Training Videos and exclusive, **Advanced Member Documentation**. This is valuable documentation that we are confident you will find it to be essential and important to your business. 


Feature Videos
---------------

To speed up the learning process, FluxPBX Members are encouraged to peruse the growing **library of Feature Videos**, to get quickly up to speed on a specific feature of FluxPBX. More videos are added on a regular basis, with the intent to help Members **become expert FluxPBX system administrators** feeling confident in their ability to manage their telephony environment with ease.

Membership Features
=====================

Gold members get access to FluxPBX's REST API. Purple members get access to Call Center Reporting and Wallboard (coming soon) Additional Member Applications will be added to all Member levels.




Membership Levels
===================


Green Level
-------------

<a href="url"><img src="https://raw.githubusercontent.com/Len-PGH/fluxpbx-docs/29d150e291f3f76199402d4eaee39ca501ccf1fa/source/_static/images/fusionpbx_member_emblem_md_green.png" align="center" height="350" width="250" ></a>


The **Green** level Membership allows one individual access to all the following benefits. Some benefits are described in detail in the Membership Benefits section below. FluxPBX is critical to your business, so becoming a Member is a crucial step in protecting your investment.

* News & Updates
* Official Training Videos
* Member Documentation
* Feature Videos
* Advanced Bug Reporting
* Monthly Continuing Education
* Additional Member Applications *(Coming Soon)*


**Price $100.00 USD Monthly** 

---
---

Blue Level
------------

<a href="url"><img src="https://raw.githubusercontent.com/Len-PGH/fluxpbx-docs/master/source/_static/images/fusionpbx_member_emblem_xl_blue.png" align="center" height="350" width="250" ></a>

The Blue level Membership allows one individual access to the Green level benefits, plus includes OVER AN HOUR of Official Support each month. FluxPBX is critical component of your business, so access to support when you need it is crucial.

* News & Updates
* Official Training Videos
* Member Documentation
* Feature Videos
* Advanced Bug Reporting
* Monthly Continuing Education
* Rebranding (White Label)
* Rebranding Training

* Up to 1.5 HOURS of Official Support Each Month
* Additional Member Applications (Coming Soon)

**Price $300.00 USD Monthly**

Purple Level
-------------

<a href="url"><img src="https://raw.githubusercontent.com/Len-PGH/fluxpbx-docs/29d150e291f3f76199402d4eaee39ca501ccf1fa/source/_static/images/fusionpbx_member_emblem_md_purple.png" align="center" height="350" width="250" ></a>

The **Purple** level Membership allows one individual access to the Green level benefits, plus includes up to THREE (3) HOURS of Official Support each month, and the use† of the advanced FluxPBX Call Center applications.

* News & Updates
* Official Training Videos
* Member Documentation
* Feature Videos
* Advanced Bug Reporting
* Monthly Continuing Education

* Call Center Wallboard Application†
* Call Center Summary (Reporting) Application†
* Up to 3 Hours of Official Priority Support Each Month
* Additional Member Applications (Coming Soon)


**Price $500.00 USD Monthly**

*† Up to three (3) company-owned servers (additional licensing available).*

---
---


Gold Level
-------------

<a href="url"><img src="https://github.com/Len-PGH/fluxpbx-docs/blob/29d150e291f3f76199402d4eaee39ca501ccf1fa/source/_static/images/fusionpbx_member_emblem_md_gold.png" align="center" height="350" width="250" ></a>

The **Gold** level Membership includes all the benefits of the Green and Purple membership levels, plus access for up to three (3) employees from your organization, up to SIX (6) HOURS of Official Support each month, and access to the FluxPBX REST API.

* News & Updates
* Official Training Videos
* Member Documentation
* Feature Videos
* Advanced Bug Reporting
* Monthly Continuing Education

* Call Center Wallboard Application‡
* Call Center Summary (Reporting) Application‡
* 3 Hours of Official Priority Support Each Month

* Up to Three (3) Users from Your Business
* 3 MORE Hours of Official Priority Support
* (Up to 6 Monthly)
* FluxPBX REST API
* Additional Member Applications (Coming Soon)


**Price $1000.00 USD Monthly**

*‡ Up to six (6) company-owned servers (additional licensing available).*

---
---

**Becoming a FluxPBX Member requires a ONE (1) YEAR COMMITMENT (to be understood as 12 consecutive months) from the date you join. Failure to maintain a valid payment method during this period may result in your membership being permanently terminated, and all support options forfeited. Following the commitment term, your membership will continue automatically on a monthly basis, but you may cancel at any time. Note: If you have paid for, and participated in, an Official FluxPBX Training course, the standard 1-year commitment does not apply.**

---
---


Free Support
--------------------------------------
We provide several avenues for you to get your system up and running on your own and learn the basics of the system.

1. [Youtube Channel](https://www.youtube.com/channel/UCN5j2ITmjua1MfjGR8jX9TA)
2. [Documentation](https://docs.fluxpbx.com)
3. [How to Contribute](https://github.com/FluxPBX/opensource)

Commercial Support
--------------------------------------
These options support the project and cover any kind of help you might need from architecture, installation, best practices, troubleshooting, custom feature programming, and training.

1. [Commercial Paid Support](https://flux.net.br/support)
2. [Custom Feature Development](https://flux.net.br/support)
3. [Admin Training](https://flux.net.br)
4. [Advanced Training](https://flux.net.br)
5. [Developer Training](https://flux.net.br)


Community
--------------------------------------
We have a pretty thriving community. You can find us here:

- [Twitter](https://twitter.com/fluxpbx)
- [Website](https://flux.net.br)

Contributing
---------------------------------------

### Requirements
It's easy to contribute to FluxPBX the only thing we ask before accepting your pull request is that you sign a Contributor License Agreement.
We ask that you sign the Contributor License Agreement for the following reasons:

1. It protects FluxPBX by you guaranteeing that your contributions are yours to contribute and not the property of an employer or something found on the web.
2. It protects you from using code that belongs to others that is subject unfriendly licensing.

### How to Contribute
* [The Quick Way](https://github.com/FluxPBX/opensource/blob/master/sign-cla.md) - Step by step instructions to contribute to FluxPBX with links to our CLA and how to submit pull requests.
* [The FluxPBX Contribution Site](https://github.com/FluxPBX/opensource) - The full repo with more information for the curious.


