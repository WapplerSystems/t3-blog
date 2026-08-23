# wapplersystems/blog

Fork von [t3g/blog](https://github.com/TYPO3GmbH/blog) mit eingefalteter Bruecken-Extension
`t3bootstrap/t3bootstrap-blog`. Der Fork ersetzt beide Pakete per Composer `replace`.

| | |
|---|---|
| Composer-Paket | `wapplersystems/blog` |
| Extension-Key | `ws_blog` |
| PHP-Namespace | `WapplerSystems\Blog` |
| Branch | `release/v14` |
| Upstream | `https://github.com/TYPO3GmbH/blog`, zuletzt gemergt: v14.0.1 |

## Was gegenueber Upstream anders ist

* **Getrennte Ablageordner je Datensatztyp.** Statt eines `persistence.storagePid` gibt es
  `settings.postsPid`, `categoriesPid`, `tagsPid`, `authorsPid`. Wirksam sind derzeit
  `categoriesPid` und `tagsPid` (Category- und TagRepository); `postsPid` und `authorsPid`
  sind definiert, werden aber noch nicht gelesen.
* **Uebersetzte Beitraege in `listByDemand`.** Bei Sprache > 0 wird gegen `l10n_parent`
  statt gegen `uid` eingeschraenkt.
* **Backend-PageLayout-Header ohne Extbase.** `BlogPostHeaderContentRenderer` laedt die
  Beitragsdaten per DBAL; mit dem Extbase-Repository dauerte die Seitenmodul-Ansicht auf
  grossen Installationen zweistellige Sekunden.
* **Bootstrap-5-Rendering** aus der Bruecke: eigener Template-Baum unter
  `Resources/Private/{Layouts,Partials,Templates}/Blog/`, Site Set `t3bootstrap/blog`,
  Backend-Layouts, SCSS-Komponenten, Ajax-Nachladeseiten.
* **Kommentare fuer eingeloggte Frontend-Benutzer**: `FrontendUserCommentFormFactory` und
  `FrontendUserCommentFormFinisher`, dazu `Comment::$author` auf `fe_users`. Der
  Upstream-Pfad fuer anonyme Kommentare mit Captcha bleibt unveraendert daneben bestehen.

## Performance

Auf der Blog-Uebersicht (12 dargestellte Beitraege) kostete ein ungecachter
Seitenaufbau urspruenglich 5,9 Sekunden und 5.244 SQL-Statements, eine normale
Inhaltsseite derselben Installation 1,3 Sekunden und 472 Statements.

Ursache waren nicht die Abfragen selbst, sondern ihre Zahl: die
ObjectStorage-Relationen der Modelle trugen `@Extbase\ORM\Lazy` im Doc-Block.
**TYPO3 v14 liest die Lazy-Markierung nur noch als PHP-Attribut**
(`ClassSchema::reflectProperties` wertet `\ReflectionProperty::getAttributes()`
aus, Doc-Bloecke werden nicht mehr geparst). Saemtliche Relationen waren damit
eager, und jeder hydrierte Beitrag zog drei MM-Abfragen plus eine
Kommentar-Abfrage nach - letztere braucht eine Listenansicht nie.

Nach der Umstellung auf `#[Lazy]`:

| | vorher | nachher |
|---|---|---|
| SQL-Statements | 5.244 | 985 |
| TTFB ungecacht | 5,9 s | 2,0 s |
| `SELECT * FROM pages WHERE uid = ?` | 1.340 | 36 |

Der Fehler steckt genauso im Upstream - ein Kandidat fuer einen Pull Request.

Wer hier weiter optimiert: messen laesst sich das am ehrlichsten ueber das
MySQL-General-Log. Achtung, mysqli protokolliert vorbereitete Anweisungen als
`Prepare`/`Execute`, nicht als `Query` - ein Filter auf `command_type='Query'`
zeigt faelschlich fast nichts an.

## Fallstricke beim Umstieg von blog/t3bootstrap_blog

1. **Der Extbase-Extensionname bleibt `Blog`.** CType (`blog_posts`, `blog_category`, ...),
   Plugin-Signaturen und `plugin.tx_blog` sind unveraendert - bestehende Inhaltselemente und
   Site-Settings laufen weiter. Nur der Extension-Key heisst `ws_blog`.
2. **`<f:translate>` braucht `extensionName="WsBlog"`.** Ohne das Attribut loest Fluid ueber
   den Extbase-Extensionnamen auf, also ueber die Label-Domain `blog.messages` - die gibt es
   nicht mehr, und die Labels verschwinden **still**. Projekte mit eigenen Blog-Templates
   muessen das nachziehen. Alternativ vollqualifizierte Keys `LLL:EXT:ws_blog/...` verwenden.
3. **Alte Klassennamen** aus `T3G\AgencyPack\Blog` und `T3Bootstrap\Blog` funktionieren
   weiter: `Migrations/Code/ClassAliasMap.php` fuer den Autoloader, zusaetzlich
   Container-Aliase in `Configuration/Services.yaml`, damit
   `GeneralUtility::makeInstance()` eine Instanz **mit** Injection liefert.
4. **Uebersetzungen liegen im Paket.** Sprachpakete vom TYPO3-Translation-Server haengen am
   Extension-Key und wird es fuer diesen Fork nie geben. Die deutschen Labels sind als
   `de.*.xlf` eingecheckt. Weitere Sprachen muessen ebenso ins Paket.
5. **Upgrade-Wizard `ExtensionKeyReferenceUpdate`** schreibt in der Datenbank verbliebene
   `EXT:blog/`- und `EXT:t3bootstrap_blog/`-Pfade um (FlexForms, TSconfig,
   TypoScript-Datensaetze, Backend-Layouts). Auf jeder Instanz einmal ausfuehren.

## Upstream-Merges

```bash
git remote add upstream https://github.com/TYPO3GmbH/blog.git
git fetch upstream
git merge upstream/v14.0
```

Die Historie des Forks enthaelt die Upstream-Historie, Merges bleiben deshalb normale
Drei-Wege-Merges. Konflikte sind bisher auf die oben genannten Abweichungen beschraenkt.
