# korppi-nettisivut
Pyynnöstä luodut henkilöstövuokrausyrityksen nettisivut, jotka sitemmin ovat poistuneet käytöstä.
Git repo on luotu erikseen vähän "leikkaa liimaa" ratkaisulla kasaten sivut taas kokoon varmuuskopioista.
## Toiminta
Perus nettisivut mobiiliversioineen (responsiivinen) ja niiden toteutus "omalla" koodilla. Omalla koodilla tarkoitetaan, ettei erillisiä kirjastoja olla oikein käytetty. JavaScriptissä JQuery taitaa olla ainoana.
Lomakkeiden toiminta toteutettu JavaScriptillä ja PHP:lla. Tietokantana toimii MySQL.
Lomakkeiden lähetyksen yhteydessä lähetetään käyttäjälle myös sähköpostia.
## Sivujen ulkonäköä ja toimintaa
### Protoilu etusivu
Sivut toteutettiin asiakkaan kanssa ensin konseptoiden.
Tehtiin sivuille suuntaa antava prototyyppi Figmassa, joka sitten toteutettiin käytännössä.

Etusivun "hero":
![Sivujen etusivua kuva 1 Figmasta](https://raw.githubusercontent.com/Nyyri/korppi-nettisivut/main/readme/figma/figma_etusivu_1.JPG)

Yrityksen tiedot lyhyesti:
![Sivujen etusivua kuva 2 Figmasta](https://raw.githubusercontent.com/Nyyri/korppi-nettisivut/main/readme/figma/figma_etusivu_2.JPG)

Arvokortti:<br/>
![Sivujen etusivua kuva 3 Figmasta](https://raw.githubusercontent.com/Nyyri/korppi-nettisivut/main/readme/figma/figma_etusivu_3.JPG)

Footer:
![Sivujen etusivua kuva 4 Figmasta](https://raw.githubusercontent.com/Nyyri/korppi-nettisivut/main/readme/figma/figma_etusivu_4.JPG)

### Toteutus
Sivujen ulkonäkö (työpöytä):
![Sivujen ulkonäkö työpöytä gif](https://raw.githubusercontent.com/Nyyri/korppi-nettisivut/main/readme/gifs/sivut.gif)

Sivujen ulkonäkö (mobiili):<br/>
![Sivujen ulkonäkö mobiili gif](https://raw.githubusercontent.com/Nyyri/korppi-nettisivut/main/readme/gifs/sivut_mobiili.gif)
## Lomakkeet
Lomakkeiden front-end toimii JavaScriptillä jQuery-kirjastoa käyttäen. Lomakkeet validoitaan ja lähetetään AJAXia käyttäen sille tehdylle .php tiedostolle. Lähetetyt tiedot validoitaan vielä php:n puolella, jonka jälkeen tiedot tallennetaan tietokantaan ja käyttäjälle sekä ylläpidolle lähetetään varmistussähköpostit. Toteutuksessa käytettiin PHPMaileria.

JavaScript ja PHP tiedostoista olisi voinut tehdä "modulaarisemmat", ettei koodi toistuisi tiedostojen välillä niin paljon. Tarkoituksena oli saada toimiva kokonaisuus mahdollisimman pian, joten tämä jäi jatkokehitykseen.

### Lomakkeiden validointia Front End
