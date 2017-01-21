@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row">
          <div class="col-lg-12">
           <h1>New tracks available for downloading</h1>
          </div>
        </div>
        <div class="row">
          <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
              <div class="jumbotron" style="padding:0;">
                  <div class="container-fluid" style="padding:0;">
                    <div class="table-responsive m-0">
                      <table class="table table-hover m-0">
                        <tbody>
                          <tr>
                              <th></th>
                              <th class="hidden-xs"></th>
                              <th class="text-center">Title</th>
                              <th class="text-center">Artist</th>
                              <th class="text-center">Genre</th>
                              <th class="text-center hidden-xs">Speed</th>
                              <th class="text-center hidden-xs">Label</th>
                              <th class="text-center hidden-xs">Release</th>
                              <th></th>
                          </tr>
                           @foreach ($tracks as $track)
                            <tr class="text-center">
                              <td class="w-74">
                                    <a href="javascript:void(0)" onclick="aud_play_pause(this)">
                                        <h4 class="mt-20">
                                          <i class="control fa fa-play" aria-hidden="true"></i>
                                        </h4>
                                        <audio class="xnine-player" src="{{ $track -> preview }}" preload="auto"></audio>
                                    </a>
                              </td>
                              <td class="p-0 hidden-xs w-74">
                                  <img src="{{ $track -> cover }}" alt="..." class="img-responsive img-74">
                              </td>
                              <td class="hidden-xs">
                                  <h5 class="mt-22"><a href="tracks/{{ $track -> id }}">{{ $track -> title}}</a></h5>
                              </td>
                              <td class="visible-xs">
                                  <h5 class="mt-22"><a href="tracks/{{ $track -> id }}">{{ $track -> title}}</a></h5>
                              </td>
                              <td>
                                  <h5 class="mt-22"><a href="#">{{ $track -> artist}}</a></h5>
                              </td>
                              <td>
                                  <h6 class="mt-25"><a href="#">{{ $track -> genre}}</a></h6>
                              </td>
                              <td class="hidden-xs">
                                  <h6 class="mt-25">{{ $track -> bpm}}</h6>
                              </td>
                              <td class="hidden-xs">
                                  <h5 class="mt-22"><a href="#">{{ $track -> label}}</a></h5>
                              </td>
                              <td class="hidden-xs w-74">
                                  <h6 class="mt-25">{{ $track -> release}}</h6>
                              </td>
                              <td class="w-74 text-center">
                                @if ($track -> track === NULL)
                                    <a href="tracks/{{ $track -> id }}" class="upload">
                                        <h4 class="mt-20">
                                          <i class="fa fa-upload fa-warning" aria-hidden="true"></i>
                                      </h4>
                                    </a>
                                      
                                @else
                                <a href="tracks/{{ $track -> id }}/download" class="download">
                                        <h4 class="mt-20">
                                          <i class="fa fa-download fa-success" aria-hidden="true"></i>
                                      </h4>
                                    </a>
                                @endif
                              </td>
                            </tr>
                            @endforeach
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              <?php echo $tracks->render(); ?>
          </div>
      </div>


    </div>

@endsection
    