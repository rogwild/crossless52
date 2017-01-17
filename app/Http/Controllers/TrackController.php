<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Track;
use App\User;
use App\TopTrack;
use App\WrongTracks;
use Auth;
use DB;
use Response;
use Illuminate\Support\Facades\Input;
use Storage;
use File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;

class TrackController extends Controller
{
    public function index($id)
    {
        $track = Track::find($id);
        $label = $track -> label;
        $number = $track -> top_track_id;
        $tracks = Track::where('label','!=',$label)->where('track','!=',NULL)->where('top_track_id','!=',$number)->orderBy('updated_at', 'desc')->paginate(5);
        $labeltracks = Track::where('label','=',$label)->where('top_track_id','!=',$number)->where('track','!=',NULL)->orderBy('updated_at', 'desc')->paginate(5);
        return view('track', compact('track', 'tracks'))->with('labeltracks', $labeltracks);
    }
    
    public function ChooseUploadFile($id)
    {
        if (Auth::check()) {
            Auth::user()->name;
            $track = Track::find($id);
            //echo $track->title;
            return view('uploadtrack')->with('track', $track);
        }
        else {
            return redirect('/login');
        }
    }
    
    public function UploadFile(Request $request, Track $track, $id)
    {
        $track = Track::find($id);
        $track-> user_id = Auth::id();
        
        /*$file = $request -> song;
        $coverfilename = $request['name'].'.jpg';
        $track -> track = $coverfilename;
        Storage::disk('local')->put($coverfilename, File::get($file));
        $track->save();*/
        $trackfile = $request->file('track');
        $v = Validator::make($request->all(), [
            'track' => 'required|mimes:wav'
        ]);
        if ($v->fails())
        {
            echo 'Your file is not WAV format';
        }
        else {
            $trackname = $track->artist.'- '.$track->title.'('.' '.$track->remixer.' '.')'.'.wav';
            $track -> track = $trackname;
                Storage::disk('local')->put($trackname, File::get($trackfile));
            $track->save();
            $user = Auth::user();
            $user->points += 5;
            $user->save();

            return redirect('/');
        }
        
    }
    
    public function create(Request $request, Track $track, User $user)
    {
        $beat = $request['html'];
        $html = new \Htmldom($beat);
        //$track = new Track;
        
        $title=$html->find('div.interior-title h1', 0)->plaintext;
        $remixer=$html->find('div.interior-title h1.remixed', 0)->plaintext;
        foreach($html->find('div.interior-track-artists a') as $artist) {
            $artist = $artist->innertext.' ';
        }
        $release=$html->find('li.interior-track-released span.value', 0)->plaintext;
        $bpm=$html->find('li.interior-track-bpm span.value', 0)->plaintext;
        $key=$html->find('li.interior-track-key span.value', 0)->plaintext;
        $genre=$html->find('li.interior-track-genre span.value', 0)->plaintext;
        $label=$html->find('li.interior-track-labels span.value', 0)->plaintext;
        $img=$html->find('img.interior-track-release-artwork', 0)->getAttribute('src');;
        $number=$html->find('button.playable-play',0)->getAttribute('data-track');
        //$track = Track::find($id);
        $audio_link="https://geo-samples.beatport.com/lofi/$number.LOFI.mp3";
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $row = Track::where('top_track_id','=',$number)->count();
        if ($row === 0) {
           $track = Track::create(['title' => $title, 
                                'user_id' => NULL, 
                                'top_track_id' => $number, 
                                'artist' => $artist, 
                                'genre' => $genre, 
                                'bpm' => $bpm, 
                                'key' => $key, 
                                'cover' => $img,
                                'remixer' => $remixer,
                                'label' => $label,
                                'release' => $release, 
                                'preview' => $audio_link,
                                /*'link' => $beat*/]);
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        return view('track')->with('track', $track); 
        }
        else {
            $track = Track::where('top_track_id',$number)->first();
            return view('track')->with('track', $track);
            
        }
    }
    
    public function TopTrack(Track $track, TopTrack $topTrack)
    {
        if (Auth::id() === 1) {
        $beat = 'https://www.beatport.com/top-100';
        $html = new \Htmldom($beat);
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('top_tracks')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        foreach($html->find('li.bucket-item') as $track) {
            $number = $track->find('button.track-queue',0)->getAttribute('data-track');
            $top = $track->find('div.buk-track-num',0)->plaintext;
            $title = $track->find('span.buk-track-primary-title',0)->plaintext;
            $topTrack = TopTrack::create(['title' => $title,
                                'id' => $number, 
                                'top' => $top]);
            echo $top.' '.$title.'<br>';
        }
        }
        else {
            return redirect('/');
        }
        //$parser->TopTrack();
    }
    
    public function destroy($id)
    {
        $track = Track::findOrFail($id);
        $track->delete();
        return redirect('/');
    }
    
    public function wrong($id, WrongTracks $wrongTrack)
    {
        if (Auth::check()) {
        $track = Track::find($id);
        $title = $track->title;
        $number = $track->top_track_id ;
        $row = WrongTracks::where('id','=',$number)->count();
        if ($row === 0) {
        $wrongTracks = WrongTracks::create(['title' => $title,
                                'id' => $number]);
        return redirect('/');
        }
        else {
            return redirect('/');
        }
        }
        else {
            return redirect('/login');
        }
        //return redirect('/');
    }
    
    public function download($id)
    {
        if (Auth::check()) {
        $user = Auth::user();
        //$points = $user->points;
        if ($user->points >= 1) {
            $track = Track::findOrFail($id);
            $trackname = $track->track;
            //$filePath = 'app/tracks/';
            $pathToFile = storage_path('app/tracks/'.$trackname);
            $user->points -= 1;
            $user->save();
            return response()->download($pathToFile);
        }
        else {
            //$tracks = Track::where('track','=',NULL)->orderBy('created_at', 'desc')->simplePaginate(15);
            return redirect('/earnpoints')/*view('earnpoints', compact('tracks'))*/;
        }
        }
        else {
            return redirect('/login');
        }
    }
    
    public function earnpoints() {
        if (Auth::check()) {
            $tracks = Track::where('track','=',NULL)->orderBy('created_at', 'desc')->simplePaginate(15);
            return view('earnpoints', compact('tracks'));
        }
        else {
            return redirect('/login');
        }
    }
    
    public function ParseNewTrack(Request $request)
    {
        return view('inputparser');
    }
    
    /*public function ShowNewTrack(Request $request)
    {
        $html = $request->input('html');
        return view('showtrack');
    }*/
}
