TrilobitPkgBundle
==============================================

E-Mail-Verschlüsselung wird verwendet, um vertrauliche Informationen per E-Mail vom Absender zum Empfänger zu schicken.

Die E-Mail-Verschlüsselung geht oft einher mit der digitalen Signatur und wird in vielen Standards wie X.509 oder PGP auch tatsächlich mit ihr kombiniert. Das Ziel einer digital signierten E-Mail ist es, Informationen so vom Absender zum Empfänger zu schicken, dass der Sender eindeutig feststellbar ist und niemand die E-Mail unbemerkt auf dem Weg vom Sender zum Empfänger manipulieren kann. Die E-Mail-Signatur befriedigt somit das Bedürfnis nach Authentizität und Integrität, stellt jedoch nicht die Vertraulichkeit sicher; hierzu benötigt es Verschlüsselung.

Die häufig angetroffene Methode, bei der E-Mail Vertraulichkeit und Authentizität zu erreichen, ist die PKI-basierte E-Mail-Verschlüsselung und -Signatur. PKI steht für Public-Key-Infrastruktur. Bei der PKI-basierten E-Mail-Verschlüsselung und -Signatur kommt fast immer einer der zwei folgenden Standards zum Einsatz:

* S/MIME: Secure / Multipurpose Internet Mail Extensions
* OpenPGP: Open Pretty Good Privacy

Mit Public-Key-Infrastruktur (PKI, englisch public key infrastructure) bezeichnet man in der Kryptologie ein System, das digitale Zertifikate ausstellen, verteilen und prüfen kann. Die innerhalb einer PKI ausgestellten Zertifikate werden zur Absicherung rechnergestützter Kommunikation verwendet.

Mit Hilfe eines asymmetrischen Kryptosystems können Nachrichten in einem Netzwerk digital signiert und verschlüsselt werden. Sichere Kryptosysteme können bei geeigneter Wahl der Parameter (z. B. der Schlüssellänge) auch bei Kenntnis des Verfahrens (vgl. Kerckhoffs’ Prinzip) zumindest nach heutigem Kenntnisstand[1] nicht in überschaubarer Zeit gebrochen werden.

In asymmetrischen Kryptosystemen benötigt der Sender für eine verschlüsselte Übermittlung den öffentlichen Schlüssel (Public Key) des Empfängers. Dieser könnte z. B. per E-Mail versandt oder von einer Web-Seite heruntergeladen werden. Dabei muss sichergestellt sein, dass es sich tatsächlich um den Schlüssel des Empfängers handelt und nicht um eine Fälschung eines Betrügers.

Das TrilobitPkgBundle kann auf diese Infrastruktur zugreifen und den öffentlichen Schlüssel zu einer E-MAil-Adresse auslesen und ausgeben.

---

E-Mail encryption is used to send confidential information by email from the sender to the recipient.

Email encryption often goes hand in hand with the digital signature and is actually combined with it in many standards such as X.509 or PGP. The aim of a digitally signed email is to send information from the sender to the recipient in such a way that the sender can be clearly identified and no one can manipulate the email unnoticed on its way from the sender to the recipient. The email signature thus satisfies the need for authenticity and integrity, but does not ensure confidentiality; this requires encryption. 

The most common encountered method of achieving confidentiality and authenticity in email is PKI-based email encryption and signature. PKI stands for public key infrastructure. PKI-based email encryption and signature almost always uses one of the following two standards:

S/MIME: Secure / Multipurpose Internet Mail Extensions
OpenPGP: Open Pretty Good Privacy
In cryptology, public key infrastructure (PKI) is a system that can issue, distribute and verify digital certificates. The certificates issued within a PKI are used to secure computer-supported communication.

Using an asymmetric cryptosystem, messages in a network can be digitally signed and encrypted. Secure cryptosystems cannot be broken within a reasonable amount of time, at least according to the current state of knowledge [1], if the parameters (e.g. key length) are selected appropriately, even if the method is known (see Kerckhoff's principle). 

In asymmetric cryptosystems, the sender needs the recipient's public key for encrypted transmission. This could e.g. sent via email or downloaded from a website. It must be ensured that it is actually the recipient's key and not a forgery by a fraudster.

The TrilobitPkgBundle can access this infrastructure and read and output the public key to an email address.

Quellen:
* https://de.wikipedia.org/wiki/E-Mail-Verschl%C3%BCsselung
* https://de.wikipedia.org/wiki/Public-Key-Infrastruktur


Backend Ausschnitt
------------

![Backend Ausschnitt](docs/images/pkg_be.png?raw=true "TrilobitPkgBundle")


Beispielhafte Ausgabe im Frontend
------------

<img src="./docs/images/pkg_fe.png" alt="Backend Ausschnitt" title="TrilobitPkgBundle" width="25%">


Installation
------------

Install the extension via composer: [trilobit-gmbh/contao-pkg-bundle](https://packagist.org/packages/trilobit-gmbh/contao-pkg-bundle).


Compatibility
-------------

- Contao version ~4.9
