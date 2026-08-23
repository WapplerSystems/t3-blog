# Offene Verbesserungen

Erhoben am 2026-08-23 gegen den Stand nach dem Zusammenfuehren von `t3g/blog` und
`t3bootstrap_blog`. Grundlage war eine DeepSeek-Recherche zu Features moderner
Blog-Systeme (WordPress, Ghost, Substack, Hashnode, Medium), anschliessend jede
Behauptung gegen den Code geprueft. Von 18 Vorschlaegen haben 9 die Pruefung ueberstanden.

Massstab ist ein B2B-Fachblog mit technischem Publikum, nicht ein Publikums-Magazin.

## Lohnend

### 1. Kommentar-Threading — Aufwand S

Antworten auf Kommentare sind nicht moeglich.

Billiger als es aussieht: `tx_blog_domain_model_comment` traegt bereits `parentid` und
`parenttable`, und `parenttable` wird nirgends im Code ausgewertet — das Schema ist also
schon polymorph angelegt. Es braucht **kein** neues Feld, sondern:

* `parenttable = 'tx_blog_domain_model_comment'` als zweiten erlaubten Wert behandeln,
* `CommentRepository::findAllByPost()` erweitern, damit Antworten nicht als
  Wurzelkommentare auftauchen,
* rekursives Partial fuer die Darstellung,
* maximale Tiefe als Setting.

### 2. Sitemap-Provider fuer Kategorie-, Tag- und Autorenseiten — Aufwand S

Beitraege stehen als Seiten laengst in der XML-Sitemap, die EXT:seo aus dem Seitenbaum
erzeugt. Die Uebersichtsseiten fehlen dort — also genau die Seiten, ueber die Fachbegriffe
ranken. Noetig sind eigene `XmlSitemapDataProvider`-Klassen fuer Kategorien, Tags, Autoren
und Archiv plus Registrierung ueber die EXT:seo-Konfiguration.

### 3. Inhaltsverzeichnis — Aufwand S

Kein Verzeichnis aus `h2`/`h3` mit Ankerlinks. Bei langen Fachartikeln erwartet, und die
Ankerlinks liefern Suchmaschinen zusaetzlich Sprungmarken. Ein ViewHelper, der den
gerenderten Beitragsinhalt parst, Ueberschriften mit `id` versieht und daraus die Liste
baut; Tiefe als Setting.

### 4. Newsletter-Abonnement — Aufwand M

Kein Abo neuer Beitraege, keine Abonnentenverwaltung, kein Double-Opt-in.

Vor einer eigenen Versandlogik lohnt die billigere Variante: ein Newsletter-Feed je
Kategorie, den ein bestehendes Mailing-Werkzeug abholt. Erst wenn das nicht reicht, eine
Tabelle `tx_blog_domain_model_subscriber` (email, token, confirmed, Kategorien als MM),
Double-Opt-in ueber EXT:form und ein Scheduler-Task fuer den Versand.

### 5. Kommentar-Abonnement — Aufwand M

Benachrichtigung bei Antworten oder neuen Kommentaren. Sinnvoll erst zusammen mit
Punkt 1, sonst gibt es nichts zu abonnieren ausser dem Gesamtstrom.

### 6. Podcast-Feed — Aufwand M

Kein Audio-Feld am Beitrag, kein RSS mit `<enclosure>` und iTunes-Tags. Nur angehen, wenn
Audio tatsaechlich geplant ist.

## Nische — erst danach

* **Lesezeichen** fuer angemeldete Frontend-Benutzer (M). Fuer ein Publikum mit
  Login-Bereich naheliegender als Likes.
* **Reaktionen/Likes** (M).
* **Webmentions/Pingbacks** (L).

## Geprueft und verworfen

Diese Vorschlaege kamen aus der Recherche, sind aber bereits abgedeckt. Hier notiert, damit
sie nicht erneut aufschlagen:

| Vorschlag | Warum hinfaellig |
|---|---|
| Zugriffsbeschraenkung (Gated Content) | `pages.fe_group` ist Core, Beitraege sind Seiten. Offen waere allenfalls ein Teaser-Modus statt hartem Ausblenden. |
| Social-Sharing-Buttons | Im Projekt-Template (`linear_blog`, `Templates/Page/BlogArticle.html`) vorhanden. |
| PDF-Export / Druckansicht | Projektseitig ueber `wapplersystems/pdflip`. |
| Blog-Suche | `wapplersystems/meilisearch` indexiert Seiten, Beitraege sind Seiten. |
| Mehrsprachigkeit | TYPO3-Core plus EXT:seo fuer hreflang; der Fork hat zusaetzlich den `l10n_parent`-Fix in `listByDemand`. |
| oEmbed | Das Core-Medienelement deckt YouTube und Vimeo ab. |
| Code-Syntax-Highlighting | Tatsaechlich nirgends vorhanden, gehoert aber ins RTE beziehungsweise Content-Element und damit nach `t3bootstrap`, nicht in diese Extension. |
| Analytics-Events | Projektweit ueber Matomo geloest. |
