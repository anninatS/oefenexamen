# DreamScape Interactive - User Stories

## Definition of Done

- Code is gecommit naar GitHub repository

- Database queries zijn geoptimaliseerd

- Documentatie is bijgewerkt

- Functionaliteit werkt in development omgeving

- Security maatregelen zijn geïmplementeerd

## Epics & User Stories

### Epic: Gebruikersbeheer

#### Registratie
Als nieuwe speler wil ik mezelf kunnen registreren, zodat ik toegang krijg tot het spel.

**Acceptatiecriteria:**

- Gebruiker kan account aanmaken met unieke gebruikersnaam

- Wachtwoord moet veilig opgeslagen worden

- Email moet geldig formaat zijn

- Gebruiker krijgt standaard 'speler' rol

- Gebruiker krijgt bevestiging na succesvolle registratie

#### Authenticatie
Als geregistreerde gebruiker wil ik kunnen inloggen, zodat ik toegang krijg tot mijn account.

**Acceptatiecriteria:**

- Gebruiker kan inloggen met gebruikersnaam/email en wachtwoord

- Ongeldige inloggegevens tonen duidelijke foutmelding

- Succesvolle login geeft toegang tot juiste rol-specifieke functionaliteit

- Sessie wordt veilig beheerd

#### Profielbeheer
Als speler wil ik mijn profielgegevens kunnen bekijken en aanpassen, zodat deze up-to-date blijven.

**Acceptatiecriteria:**

- Gebruiker kan eigen profielgegevens inzien

- Gebruiker kan email aanpassen

- Gebruiker kan wachtwoord wijzigen

- Wijzigingen worden pas opgeslagen na bevestiging

### Epic: Itemcatalogus

#### Items Bekijken
Als speler wil ik door de itemcatalogus kunnen bladeren, zodat ik items kan ontdekken.

**Acceptatiecriteria:**


- Toon overzicht van beschikbare items

- Toon per item: naam, beschrijving, type, zeldzaamheid, statistieken

- Items zijn te filteren op itemtype en zeldzaamheid

- Statistieken (kracht, snelheid, duurzaamheid) worden duidelijk weergegeven

- Magische eigenschappen worden getoond

#### Items Zoeken
Als speler wil ik kunnen zoeken naar specifieke items, zodat ik snel vind wat ik zoek.

**Acceptatiecriteria:**

- Zoeken met filters op itemtype en zeldzaamheid

- Zoeken op statistieken

- Zoekresultaten worden overzichtelijk weergegeven

### Epic: Inventarisbeheer

#### Inventaris Bekijken
Als speler wil ik mijn persoonlijke inventaris kunnen bekijken, zodat ik overzicht heb van mijn items.

**Acceptatiecriteria:**

- Toon alle items in bezit

- Items zijn te filteren op itemtype en statistieken

- Per item worden alle details getoond

### Epic: Handelssysteem

#### Handelsverzoek Versturen
Als speler wil ik handelsverzoeken kunnen versturen, zodat ik items kan ruilen met andere spelers.

**Acceptatiecriteria:**

- Selecteren van items voor ruil

- Selecteren van doelspeler

- Versturen van handelsverzoek

- Ontvangen bevestiging van verzending

#### Handelsverzoek Behandelen
Als speler wil ik ontvangen handelsverzoeken kunnen accepteren of weigeren, zodat ik controle heb over mijn trades.

**Acceptatiecriteria:**

- Tonen van ontvangen handelsverzoeken

- Details van aangeboden items inzichtelijk

- Mogelijkheid tot accepteren/weigeren

- Bevestiging van keuze

- Automatische inventaris update na succesvolle handel

#### Handelsnotificaties
Als speler wil ik notificaties ontvangen over handelsgerelateerde activiteiten, zodat ik op de hoogte blijf.

**Acceptatiecriteria:**

- Notificatie bij nieuw handelsverzoek

- Notificatie bij geaccepteerd handelsverzoek

### Epic: Beheerderspaneel

#### Gebruikersbeheer (Admin)
Als beheerder wil ik gebruikers kunnen aanmaken, zodat ik nieuwe accounts kan creëren.

**Acceptatiecriteria:**

- Aanmaken nieuwe gebruikers

- Toewijzen van rollen

#### Itembeheer (Admin)
Als beheerder wil ik items kunnen beheren, zodat ik de game-economie kan controleren.

**Acceptatiecriteria:**

- Aanmaken nieuwe items

- Bewerken bestaande items

- Verwijderen items

- Statistieken instellen (0-100)

- Toewijzen items aan spelers

#### Item Toewijzing (Admin)
Als beheerder wil ik items kunnen toewijzen aan spelers voor bug compensatie of community rewards.

**Acceptatiecriteria:**

- Selecteren van specifieke speler

- Selecteren van specifiek item

- Toevoegen item aan spelerinventaris

#### Economie Monitoring (Admin)
Als beheerder wil ik kunnen zien hoeveel spelers een bepaald type item bezitten, zodat ik de economie in balans kan houden.

**Acceptatiecriteria:**

- Overzicht van aantal spelers per itemtype

- Filteropties voor verschillende itemtypes
