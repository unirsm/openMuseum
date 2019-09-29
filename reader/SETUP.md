
# Connessione rete wifi e contenuti interattivi

La configurazione della rete wifi avviene localmente inserendo nella partizione di boot nella memoria sd, un file chiamato: *basic_config.json*. Al primo avvio il sistema si connetterà alla rete leggendo i parametri indicati nel file.

### *File:  basic_config.json*


    [{
    	"casamuseo_id": 2,
    	"ssid": "nomerete",
	    "password": "password",
		 "server_remoto_configurazione":"http://server.web"
	    "ip_address": "192.168.189.55"
	    "gateway": "192.168.189.1"
	    "dns": "192.168.189.1",
	    "password_reader": "password_operativa"
    }]

Queste informazioni sono sufficienti a stabilire la connessione del lettore alla rete locale, e a permettere allo stesso di raggiungere il server (sia esso locale o remoto) da cui scaricare tutte le configurazioni di TAG, contenuti, interazioni. La password_reader va inserita uguale nel record del sistema online e permetterà di interagire con il disposto dalla piattaforma web. La voce "casamuseo_id" indica il riferimento della tabella online da cui recuperare i json secondo il seguente schema.

### Configurazione Remota

La configurazione remota avviene attraverso WUI: una parte di tale configurazione viene eseguita da una figura di Admin / Tecnici; si tratta perlopiù di definizione dei tag e delle CaseMuseo.
Una seconda parte verrà eseguita tendenzialmente da curatori o supporto ai curatori delle singole CaseMuseo; si tratterà di associazioni tra tag e contenuti, o tag e device (casse bt o luci).
Tutte i dati visualizzati e imputati da WUI verranno caricati e salvati su database relazionale. Tutti i JSON di configurazione vanno inseriti nella cartella */home/pi/script/json/* mentre tutti i file video audio delle rispettive sotto cartelle di */home/pi/media/*.

I seguenti esempi sono riferiti tutti alla casa museo con id 2 nel database online.

## Definizione TAG RFID

La definizione dei TAG RFID è il primo step per poter definire il sistema di interazioni. La tabella *tag_uid* con la lista dei tag interattivi.

### Files: rfid.json
*URL:  json.php?m=2&t=rfid*

    [{
	    "id_casamuseo": 1,
	    "rfid": [{
		    "id": 1,
		    "tag_uid": "AE34E1"
	    }, {
		    "id": 2,
		    "tag_uid": "FF540A"
	    }, {
		    "id": 3,
		    "tag_uid": "34567B"
	    }]
    }]

Sarà in questo modo possibile apporre ai TAG rfid una etichetta con ID corrispondente per poterli identificare univocamente e inequivocabilmente.

Imputazione fatta almeno per un primo batch di tag rfid da Admin / Tecnico.

## Definizione contenuti multimediali

I contenuti multimediali (video, audio, chiamate a server MQTT) vanno inseriti nella libreria dei contenuti multimedia.
Da parte del nodo ci sarà una interrogazione al server remoto per ottenere questa tabella, usando come parametro di richiesta l’id della CasaMuseo. La risposta ottenuta, su file multimedia.json sarà formattata come esempio seguente:


### Files: multimedia.json
*URL:  json.php?m=2&t=multimedia*

    [{
	    "id_casamuseo": 1,
	    "multimedia": [{
		    "id": 1,
		    "tipo_media": "video",
		    "descrizione_media": "Video poeta Pascoli",
		    “titolo_media”: “video_pascoli.mp4”,
		    "path_media": "ftp://<ftp_server_remoto>/<path>/video_pascoli.mp4"
	    },{
		    "id": 2,
		    "tipo_media": "audio",
		    "descrizione_media": "Testo poesia Pascoli",
		    “titolo_media”: “audio_pascoli.mp4”,
		    "path_media": "http://<webdav_server_remoto>/<path>/audio_pascoli.mp4"
	    }]

    }]

Sarà in questo modo possibile localmente generare una coda di download per i contenuti, e associarli ad un id univocamente e inequivocabilmente.

### Definizione device: neopixel spot

I neopixel spot vanno inseriti nella libreria dei device.
Da WUI l’utente inserirà l’indirizzo IP di ogni singolo device.
Ci sarà una tabella con di base le informazioni: Id_casa_museo : id : ip_indirizzo

 Da parte del nodo ci sarà una interrogazione al server remoto per ottenere questa tabella, usando come parametro di richiesta l’id della CasaMuseo. La risposta ottenuta, su file neopixelspot.json sarà formattata come esempio seguente:

### File:  neopixelspot.json
*Url:  json.php?m=2&t=neopixelspot*


    [  
       {  
          "id_casamuseo":2,
          "neopixelspot":[  
             {  
                "id":1,
                "description":"Spot 1 luce rossa",
                "ip_indirizzo":"192.168.0.123",
                "colorRGB":"#ff000"
             },
             {  
                "id":2,
                "description":"Spot 2 luce blu",
                "ip_indirizzo":"192.168.0.164",
                "colorRGB":"#0000FF"
             }
          ]
       }
    ]



Imputazione fatta da supporto locale tecnico (è necessario configurare sull’AP degli indirizzi riservati ai neopixelspot).



### Definizione device: speaker bluetooth

Gli speaker bluetooth vanno inseriti nella libreria dei device.
Da WUI l’utente inserirà il mac address del dispositivo BT (ottenuto con uno smartphone scansionando per dispositivi BT) e una descrizione dello stesso.
Ci sarà una tabella con di base le informazioni:
Id_casa_museo : id : bt_mac : bt_descrizione
Da parte del nodo ci sarà una interrogazione al server remoto per ottenere questa tabella, usando come parametro di richiesta l’id della CasaMuseo. La risposta ottenuta, su file bt_speaker.json sarà formattata come esempio seguente:

### File:  bt_speaker.json
*Url:  json.php?m=2&t=bt_speaker*

     [{
    	"id_casamuseo": 1,
    	"bt_speaker": [{
    		"id": 1,
    		"bt_mac": "00:12:23:34:45:56",
    		"bt_descrizione": "Cassa verde grande AUKEY"
    	},{
    		"id": 2,
    		"bt_mac": "98:87:AA:EE:12:DD",
    		"bt_descrizione": "Cassa nera BOSE"
    	}]
    }]







### Definizione device: IKEA TRADFRI

Aggiornamento:

Questa procedura è disattivata la configurazione va fatta da Nodered. Nello sviluppo futuro si potrà usare questa libreria

[https://github.com/ggravlingen/pytradfri](https://github.com/ggravlingen/pytradfri)


File:  tradfri.json

Url:  json.php?m=2&t=tradfri

Test: [http://lab.z14.it/giano/json.php?m=2&t=tradfri](http://lab.z14.it/giano/json.php?m=2&t=tradfri)




[{

"id_casamuseo": 1,

"tradfri": [{

"id": 1,

"ip_gateway": "192.168.0.111",

"id_bulbo": "TRADFRI bulb E27 opal 1000lm",

"bulbo_descrizione": "Luce su staffa"

},{

"id": 2,

"ip_gateway": "192.168.0.111",

"id_bulbo": "TRADFRI bulb E27 opal 1000lm 2",

"bulbo_descrizione": "Luce su cavalletto"

}]

}]



Imputazione fatta da supporto locale tecnico (è necessario configurare sull’AP dell’indirizzo del gateway).




## Interazione RFID contenuti / devices

La tabella in cui sono registrate le interazioni tra RFID e contenuti e devices è il cuore del sistema.

Da WUI l’utente potrà scegliere il tag rfid numerato e associarlo alla cassa bluetooth, piuttosto che ad un video, ad una luce o ad un suono.



Ci sarà una tabella con di base le informazioni:

Id_casa_museo : id : id_rfid : id_contenuto_device : tipo_contenuto_device



Da parte del nodo ci sarà una interrogazione al server remoto per ottenere questa tabella, usando come parametro di richiesta l’id della CasaMuseo. La risposta ottenuta, su file interactions.json sarà formattata come esempio seguente:





File:  interaction.json

Url:  json.php?m=2&t=interaction

Test: [http://lab.z14.it/giano/json.php?m=2&t=interaction](http://lab.z14.it/giano/json.php?m=2&t=interaction)




[{

"id_casamuseo":2,

"interaction":[{

"id_rfid":1,

"object":[{

"id":1,

"type":"bt_speaker",

"id_ix":1

}]

},{

"id_rfid":2,

"object":[{

"id":1,

"type":"multimedia",

"id_ix":1

},{

"id":2,

"type":"neopixelspot",

"id_ix":2

}]

},{

"id_rfid":3,

"object":[{

L’ho tolto "id":1,

"type":"bt_speaker",

"id_ix":1

},{

"id":2,

"type":"multimedia",

"id_ix":2

},{

"id":3,

"type":"neopixelspot",

"id_ix":1


},{

"id":4,

"type":"tradfri",

"id_ix":1

}]

}]

}
