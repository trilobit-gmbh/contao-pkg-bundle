TrilobitPkgBundle
==============================================

E-Mail-Verschlüsselung wird verwendet, um vertrauliche Informationen per E-Mail vom Absender zum Empfänger zu schicken.

Die E-Mail-Verschlüsselung geht oft einher mit der digitalen Signatur und wird in vielen Standards wie X.509 oder PGP auch tatsächlich mit ihr kombiniert. Das Ziel einer digital signierten E-Mail ist es, Informationen so vom Absender zum Empfänger zu schicken, dass der Sender eindeutig feststellbar ist und niemand die E-Mail unbemerkt auf dem Weg vom Sender zum Empfänger manipulieren kann. Die E-Mail-Signatur befriedigt somit das Bedürfnis nach Authentizität und Integrität, stellt jedoch nicht die Vertraulichkeit sicher; hierzu benötigt es Verschlüsselung.

Die häufig angetroffene Methode, bei der E-Mail Vertraulichkeit und Authentizität zu erreichen, ist die PKI-basierte E-Mail-Verschlüsselung und -Signatur. PKI steht für Public-Key-Infrastruktur. Bei der PKI-basierten E-Mail-Verschlüsselung und -Signatur kommt fast immer einer der zwei folgenden Standards zum Einsatz:

* S/MIME: Secure / Multipurpose Internet Mail Extensions
* OpenPGP: Open Pretty Good Privacy

Mit Public-Key-Infrastruktur (PKI, englisch public key infrastructure) bezeichnet man in der Kryptologie ein System, das digitale Zertifikate ausstellen, verteilen und prüfen kann. Die innerhalb einer PKI ausgestellten Zertifikate werden zur Absicherung rechnergestützter Kommunikation verwendet.

Das TrilobitPkgBundle kann auf diese Infrastruktur zugreifen und den öffentlichen Schlüssel zu einer E-MAil-Adresse auslesen und ausgeben.

Mit Hilfe eines asymmetrischen Kryptosystems können Nachrichten in einem Netzwerk digital signiert und verschlüsselt werden. Sichere Kryptosysteme können bei geeigneter Wahl der Parameter (z. B. der Schlüssellänge) auch bei Kenntnis des Verfahrens (vgl. Kerckhoffs’ Prinzip) zumindest nach heutigem Kenntnisstand[1] nicht in überschaubarer Zeit gebrochen werden.

In asymmetrischen Kryptosystemen benötigt der Sender für eine verschlüsselte Übermittlung den öffentlichen Schlüssel (Public Key) des Empfängers. Dieser könnte z. B. per E-Mail versandt oder von einer Web-Seite heruntergeladen werden. Dabei muss sichergestellt sein, dass es sich tatsächlich um den Schlüssel des Empfängers handelt und nicht um eine Fälschung eines Betrügers.

Hierzu dienen nun digitale Zertifikate, die die Authentizität eines öffentlichen Schlüssels und seinen zulässigen Anwendungs- und Geltungsbereich bestätigen. Das digitale Zertifikat ist selbst durch eine digitale Signatur geschützt, deren Echtheit mit dem öffentlichen Schlüssel des Ausstellers des Zertifikates geprüft werden kann.

Um die Authentizität des Ausstellerschlüssels zu prüfen, wird wiederum ein digitales Zertifikat benötigt. Auf diese Weise lässt sich eine Kette von digitalen Zertifikaten aufbauen, die jeweils die Authentizität des öffentlichen Schlüssels bestätigen, mit dem das vorhergehende Zertifikat geprüft werden kann. Eine solche Kette von Zertifikaten wird Validierungspfad oder Zertifizierungspfad genannt. Auf die Echtheit des letzten Zertifikates (und des durch dieses zertifizierten Schlüssels) müssen sich die Kommunikationspartner ohne ein weiteres Zertifikat verlassen können.


Backend Ausschnitt
------------

![Backend Ausschnitt](docs/images/pkg_be.png?raw=true "TrilobitPkgBundle")


Beispielhafte Ausgabe im Frontend
------------

![Backend Ausschnitt](docs/images/pkg_fe.png?raw=true "TrilobitPkgBundle")


Installation
------------

Install the extension via composer: [trilobit-gmbh/contao-pkg-bundle](https://packagist.org/packages/trilobit-gmbh/contao-pkg-bundle).


Compatibility
-------------

- Contao version ~4.8
