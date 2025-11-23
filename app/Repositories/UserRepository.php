<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;

class UserRepository
{
    public function addUser($userData) {
        try {
            return User::create($userData);
        } catch (QueryException $e) {
            Log::error('Can\'t add user: ' . $e->getMessage());
        }
    }

    public function editUser($userData, $userId){
        try{
            $user= User::find($userId);
            if ( $user ){
                $user->update($userData);
                return $user;
            }
            return null;

        }catch(QueryException $e){
            Log::error('Can\'t edit user: ' . $e->getMessage());
        }
    }

    public function assignFirebaseUid($userId, $firebaseUid, $email, $name) {
        try {
            $starters = [
                'cipteraptos@gmail.com',
                'jelenaasenov93@gmail.com',
                'snezafilipovic@hotmail.com',
                'tamaramagoc1987@gmail.com',
                'marko@barbell.rs',
                'milanstraxi@gmail.com',
                'jovanamarinkovic49@gmail.com',
                'ibojaripak@yahoo.com',
                'irenka.vuk@gmail.com',
                'anaaleksic206@gmail.com',
                'babkamarjana@gmail.com',
                'nexonbre@gmail.com',
                'ivanamilenkovic.iva@gmail.com',
                'jelenacubrilov@gmail.com',
                'alex.92@hotmail.rs',
                'markovicvaskica@gmail.com',
                'pepeljuga.bg@gmail.com',
                'jaca.vuckovic92@gmail.com',
                'bakicmilica75@gmail.com',
                'office@cbg.rs',
                'profognjen@gmail.com',
                'epruginic@gmail.com',
                'emilija.skovric20@gmail.com',
                'tasha1325@gmail.com',
                'dragana.marinkovic@protonmail.com',
                'andrejev00@gmail.com',
                'biljana_2005@yahoo.com',
                'kata.ntc@gmail.com',
                'mina.petrovic.sub@gmail.com',
                'suada_bas@hotmail.rs',
                'jelena111198@gmail.com',
                'vanja24104@gmail.com',
                'lveliborka@gmail.com',
                'marijanajovicic91@yahoo.co.uk',
                'uzmaya@gmail.com',
                'sanjajmo@hotmail.com',
                'ikani58@gmail.com',
                'radovankatic123@gmail.com',
                'zarkostankovic16@gmail.com',
                'analugonja@mail.com',
                'teodorasimic3@gmail.com',
                'dodaayb@gmail.com',
                'demirovicvasvija@gmil.com',
                'dragana.obradovic82@yahoo.com',
                'vasiljevickristina95@gmail.com',
                'nzlatic50@gmail.com',
                'tijanadjuric1985@gmail.com',
                'aleksa.stevic26@gmail.com',
                'duskostojic@hotmail.com',
                'majanidza@gmail.com',
                'milojkovicnata@gmail.com',
                'tinavalfilip@gmail.com',
                'elenapruginic558@gmail.com',
                'ficajovic@gmail.com',
                'marija.petkovic79@gmail.com',
                'caliope1991@gmail.com',
                'vanjagracanin81@gmail.com',
                'suzanalogo@yahoo.com',
                'mmarinkovic590@gmail.com',
                'bozicastojanovic71@gmail.com',
                'tanjanovkovic0503@gmail.com',
                'mracnatama201@gmail.com',
                'bakisnada7@gmail.com',
                'jelenasugovic@gmail.com',
                'miladinovic.suzana989@gmail.com',
                'rakicjelena15@gmail.com',
                'vesnacoralic@gmail.com',
                'mirjana.jev@gmail.com',
                'milicamarinkovic@sbb.rs',
                'marijamagdalenakuzmanovic@gmail.com',
                'pavlovic.sladjana.zeka@gmail.com',
                'nikola.ruzicic77@gmail.com',
                'maxapostar@gmail.com',
                'ergelasl@gmail.com',
                'karolinablagojevic3@gmail.com',
                'sladjas71kg@gmail.com',
                'danijelstojanovski95@gmail.com',
                'mladenovicanja0021@gmail.com',
                'miloshina33@yahoo.com',
                'ivaljujicns@gmail.com',
                'vucijak.natasa@gmail.com',
                'peladickatarina22@gmail.com',
                'borko.savic@gmail.com',
                'milan.ronchevic@gmail.com',
                'aleksic.ana007@gmail.com',
                'kaca9624@gmail.com',
                'arsov.dragana31@gmail.com',
                'ilicbojan82@live.com',
                'm.pazin@icloud.com',
                'mira.milojevic@gmail.com',
                'blaskovicsanja@gmail.com',
                'valley.lark8455@eagereverest.com',
                'sandra.sovilj@gmail.com',
                'studioton@gmail.com',
                'nadjavla@gmail.com',
                'sanjajakimovski922@gmail.com',
                'borisantonijev@gmail.com',
                'popovicvasilisa@gmail.com',
                'teic1994@gmail.com',
                'mmiletic01@gmail.com',
                'marijaradenkovic94@gmail.com',
                'severdziks@gmail.com',
                'pavlovicztn@gmail.com',
                'dusan00brankovic@gmail.com',
                'jasnanick@gmail.com',
                'janko.blagojevic@timepad.rs',
                'visnjabre@icloud.com',
                'natasamilunovic993@gmail.com',
                'milica.mandic8@gmail.com',
                'jelena.z.banjac@gmail.com',
                'kapetanamerika1994@gmail.com',
                'karolina.stojanovski11@gmail.com',
                'milos_1300@hotmail.com',
                'snezanabircakovic2010@gmail.com',
                'aleksandra.mihailovic.022@gmail.com',
                'cacas785@gmail.com',
                'sneza.jsu@gmail.com',
                'thperic@gmail.com',
                'sanja.cvijanovic@yahoo.com',
                'olivija_rogic@yahoo.com',
                'jeftict1@gmail.com',
                'mayaobrenovic@yahoo.com',
                'yeyast1@gmail.com',
                'tanjadj80@gmail.com',
                'marjanajovanovic88@gmail.com',
                'jeka_ibiza@hotmail.com',
                'n.selakovic75@gmail.com',
                'jelenamacvan@yahoo.com',
                'ackenidza123@gmail.com',
                'ihamiljkovic@gmail.com',
                'maletic.tadic95@gmail.com',
                'selenicj@gmail.com',
                'isidooora@gmail.com',
                'katarina.kaca.perendic@gmail.com',
                'nina.zderic91@gmail.com',
                'gordanastupar@hotmail.com',
                'stefanovic.olga@yahoo.com',
                'snezana-c@hotmail.com',
                'maaravucic@gmail.com',
                'almacokic@hotmail.com',
                'natasavlahovic184@yahoo.com',
                'nevenna@live.com',
                'ivadjukic75@gmail.com',
                'nikola.fungroup@gmail.com',
                'ibromuratovic57@gmail.com',
                'aleksandraaad3@gmail.com',
                'mirkostjepanovic44503@gmail.com',
                'tamara.arsovic@web.de',
                'maja.milutinovic@yahoo.com',
                'skeledzija.todor@gmail.com',
                'milan.micic84@gmail.com',
                'djordje.subasic@akovrakija.com',
                'topicevazena@gmail.com',
                'ivanamarkovic.foto@gmail.com',
                'nadjavujic1@gmail.com',
                'majajelovac87@gmail.com',
                'd.djikandic@gmail.com',
                'janaav01@gmail.com',
                'majadrobnjak@gmail.com',
                'ana.dobras.stankovic@gmail.com',
                'ivana.stevanovic86@gmail.com',
                'csasa901@gmail.com',
                'lepojevicnemanja@gmail.com',
                'damir09rajic@gmail.com',
                'silvana.selkic@gmail.com',
                'milicakocic1999@gmail.com',
                'mmilojevic115@gmail.com',
                'jeremicmilica32@gmail.com',
                'ana.nikacevic7@gmail.com',
                'niki.sovilj@gmail.com',
                'dar.mit@hotmail.com',
                'nikola.kalinovic1@gmail.com',
                'janko.blagojevic@evokegroup.rs',
                'karolinastojanovski2@gmail.com',
                'jblagoj01@gmail.com',
                'janko.tbbt@gmail.com',
            ];
            $user = User::find($userId);
            $user->firebase_uid = $firebaseUid;
            $user->email = $email;
            $user->name = $name;
            if(in_array($email, $starters)) {
                $user->type = 2;
            }
            $user->save();
        } catch (QueryException $e) {
            Log::error('Can\'t assign firebase uid: ' . $e->getMessage());
        }
    }
}
