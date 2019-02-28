<html>
      <head lang="en">
            <meta charset="UTF-8">
            <title></title>
            <script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
            <script src="https://esolz.co.in/lab3/jitsitest/libs/strophe/strophe.js"></script>
            <script src="https://esolz.co.in/lab3/jitsitest/libs/strophe/strophe.disco.min.js?v=1"></script>
            <script src="https://meet.jit.si/libs/lib-jitsi-meet.min.js"></script>
            <!--<script src="https://acc.jurislink.com/public_html/jitsifile/example.js" ></script>-->
      </head>
      <body>
            <div style="min-height: 100vh;position: relative;">
                  <div id="video1" style="text-align: center;"></div>
                  <div id="video2" style="position: absolute;top: 0px;left: 0;"></div>
                  <div id="video3" style="    position: absolute;bottom: 0;left: 0;"></div>
                  <!-- <div id="video4"></div> -->
                  <div id="myvideo" style="position: absolute;right: 0;bottom: 0;"></div>
            </div>

            <a href="#" onclick="unload()">Unload</a>
            <a href="#" onclick="switchVideo()">switchVideo</a>
            <div id="audioOutputSelectWrapper" style="display: none;">
                  Change audio output device
                  <select id="audioOutputSelect" onchange="changeAudioOutput(this)"></select>
            </div>
            <div style="display: none;">
                <iframe src="https://acc.jurislink.com/public_html/jitsifortest.php?id=12&type=4" width="640" height="480"></iframe>
            </div>
      </body>
</html>
<script>
        /* global $, JitsiMeetJS */

        const options = {
            hosts: {
                domain: 'meet.jit.si',
                muc: 'conference.meet.jit.si' // FIXME: use XEP-0030
            },
//            testing: {
//// Enables experimental simulcast support on Firefox.	57	// Enables experimental simulcast support on Firefox.
//                enableFirefoxSimulcast: false,
//
//                // P2P test mode disables automatic switching to P2P when there are 2	60	// P2P test mode disables automatic switching to P2P when there are 2
//                // participants in the conference.	61	// participants in the conference.
//                p2pTestMode: false	
//            },
            bosh: '//meet.jit.si/http-bind', // FIXME: use xep-0156 for that

            // The name of client node advertised in XEP-0115 'c' stanza
            clientNode: 'http://jitsi.org/jitsimeet',

            focus: 'focus.meet.jit.si',

//            ignoreStartMuted:true
        };

        const confOptions = {
            openBridgeChannel: true
        };

        let connection = null;
        let isJoined = false;
        let room = null;

        let localTracks = [];
        const remoteTracks = {};
        /**
         * Handles local tracks.
         * @param tracks Array with JitsiTrack objects
         */
        function onLocalTracks (tracks) {
            //        alert("local" + tracks);
            var type = getUrlParam('type');
            localTracks = tracks;
            for (let i = 0; i < localTracks.length; i++) {
                localTracks[i].addEventListener(
                    JitsiMeetJS.events.track.TRACK_AUDIO_LEVEL_CHANGED,
                    audioLevel => console.log(`Audio Level local: ${audioLevel}`));
                localTracks[i].addEventListener(
                    JitsiMeetJS.events.track.TRACK_MUTE_CHANGED,
                    () => console.log('local track muted'));
                localTracks[i].addEventListener(
                    JitsiMeetJS.events.track.LOCAL_TRACK_STOPPED,
                    () => console.log('local track stoped'));
                localTracks[i].addEventListener(
                    JitsiMeetJS.events.track.TRACK_AUDIO_OUTPUT_CHANGED,
                    deviceId =>
                    console.log(
                        `track audio output device was changed to ${deviceId}`));
//                if (localTracks[i].getType() === 'video') {
                $('#myvideo').html(`<video autoplay='1' width='300px' height='300px' id='localVideo${i}'/><audio autoplay='1' mute='false'  id='localAudio${i}' />`);
                localTracks[i].attach($(`#localVideo${i}`)[0]);
                localTracks[i].attach($(`#localAudio${i}`)[0]);
//                } else {
//                    $('#myvideo').html(
//                        ``);
//                    localTracks[i].attach($(`#localAudio${i}`)[0]);
//                }
                if (isJoined) {
                    room.addTrack(localTracks[i]);
                }
            }
        }

        /**
         * Handles remote tracks
         * @param track JitsiTrack object
         */


        function onRemoteTrackk (track) {
            //        alert(track.getParticipantId());

            var meeting_id = getUrlParam('id');
            var type = getUrlParam('type');
            if (meeting_id > "" && type > "") {
                $.ajax({
                    async: false,
                    url: "https://acc.jurislink.com/public_html/jitsifile/roomsave.php",
                    data: {participant: track.getParticipantId(), type: type, meeting_id: meeting_id},
                    type: 'GET',
                    success: function (response) {
                        //                alert('inserted');
                    }
                });
            }


            if (track.isLocal()) {
                return;
            }
            const participant = track.getParticipantId();
            /////
            if (!remoteTracks[participant]) {
                remoteTracks[participant] = [];
            }
            const idx = remoteTracks[participant].push(track);
            track.addEventListener(
                JitsiMeetJS.events.track.TRACK_AUDIO_LEVEL_CHANGED,
                audioLevel => console.log(`Audio Level remote: ${audioLevel}`));
            track.addEventListener(
                JitsiMeetJS.events.track.TRACK_MUTE_CHANGED,
                () => console.log('remote track muted'));
            track.addEventListener(
                JitsiMeetJS.events.track.LOCAL_TRACK_STOPPED,
                () => console.log('remote track stoped'));
            track.addEventListener(JitsiMeetJS.events.track.TRACK_AUDIO_OUTPUT_CHANGED,
                deviceId =>
                console.log(
                    `track audio output device was changed to ${deviceId}`));
            const id = participant + track.getType() + idx;
            //        alert(id);
//            ignoreStartMuted;
            $.ajax({
                async: false,
                url: "https://acc.jurislink.com/public_html/jitsifile/getroom.php",
                data: {participant: track.getParticipantId()},
                type: 'GET',
                success: function (response) {
                    response = JSON.parse(response);
                    //                    alert(response);
                    if (track.getType() === 'video') {
                        // (1==kiosk,2=attorney,3=invitee)
//                        alert(response.type);
                        if (type == 1) {//kiosk
                            if (response.type == 2) {//attorney

                                $('#video1').html(
                                    `<video autoplay='1' width='500px' height='500px' id='${participant}video${idx}'/><audio autoplay='1' id='${participant}audio${idx}'/>`);
                            }
                            if (response.type == 3) {//invitee

                                if ($("#video2").find('video').length > 0) {
                                    $('#video3').html(
                                        `<video autoplay='1' width='300px' height='300px' id='${participant}video${idx}'/><audio autoplay='1' id='${participant}audio${idx}'/>`);
                                } else {
                                    $('#video2').html(
                                        `<video autoplay='1' width='300px' height='300px' id='${participant}video${idx}'/><audio autoplay='1' id='${participant}audio${idx}'/>`);
                                }

                            }

                        }
                        if (type == 2) {//attorney
                            if (response.type == 1) {//kiosk

                                $('#video1').html(
                                    `<video autoplay='1' width='500px' height='500px' id='${participant}video${idx}' /><audio autoplay='1' id='${participant}audio${idx}'/>`);
                            }
                            if (response.type == 3) {//invitee

                                if ($("#video2").find('video').length > 0) {
                                    $('#video3').html(
                                        `<video autoplay='1' width='300px' height='300px' id='${participant}video${idx}' /><audio autoplay='1' id='${participant}audio${idx}'/>`);
                                } else {
                                    $('#video2').html(
                                        `<video autoplay='1' width='300px' height='300px' id='${participant}video${idx}' /><audio autoplay='1' id='${participant}audio${idx}'/>`);
                                }
                            }
                        }
                        if (type == 3) {//invitee
                            if (response.type == 1) {//kiosk

                                $('#video1').html(
                                    `<video autoplay='1' width='500px' height='500px' id='${participant}video${idx}' /><audio autoplay='1' id='${participant}audio${idx}'/>`);
                            }
                            if (response.type == 2) {//attorney

                                $('#video2').html(
                                    `<video autoplay='1' width='300px' height='300px' id='${participant}video${idx}' /><audio autoplay='1' id='${participant}audio${idx}'/>`);
                            }
                            if (response.type == 3 && $("#video2").find('video').length > 0) {
                                $('#video3').html(
                                    `<video autoplay='1' width='300px' height='300px' id='${participant}video${idx}' /><audio autoplay='1' id='${participant}audio${idx}'/>`);
                            }



                        }
                    }
                    track.attach($(`#${id}`)[0]);
                }
            });

        }

        /**
         * That function is executed when the conference is joined
         */
        function onConferenceJoined () {
//            onConnectionSuccess();
//            room.on(JitsiMeetJS.events.conference.TRACK_MUTE_CHANGED, track => {
//                console.log(`${track.getType()} - ${track.isMuted()}`);
//            });
            console.log('conference joined!');
            isJoined = true;
            for (let i = 0; i < localTracks.length; i++) {
                room.addTrack(localTracks[i]);
            }
        }

        /**
         *
         * @param id
         */
        function onUserLeft (id) {
            console.log('user left');
            if (!remoteTracks[id]) {
                return;
            }
            const tracks = remoteTracks[id];

            for (let i = 0; i < tracks.length; i++) {
                tracks[i].detach($(`#${id}${tracks[i].getType()}`));

            }
        }

        /**
         * That function is called when connection is established successfully
         */

        function getUrlParam (name) {
            var results = new RegExp('[\\?&]' + name + '=([^&#]*)').exec(window.location.href);
            return (results && results[1]) || undefined;
        }



        function onConnectionSuccess () {
            var id = getUrlParam('id');
            //        alert(typeof id);
            if (id > "") {
                room = connection.initJitsiConference(id, confOptions);
            } else {
                room = connection.initJitsiConference('jitsi', confOptions);
            }
            room.on(JitsiMeetJS.events.conference.TRACK_ADDED, onRemoteTrackk);
            room.on(JitsiMeetJS.events.conference.TRACK_REMOVED, track => {
                console.log(`track removed!!!${track}`);
            });
            room.on(
                JitsiMeetJS.events.conference.CONFERENCE_JOINED,
                onConferenceJoined);
            room.on(JitsiMeetJS.events.conference.USER_JOINED, id => {
                console.log('user join');
                remoteTracks[id] = [];
            });
            room.on(JitsiMeetJS.events.conference.USER_LEFT, onUserLeft);
            room.on(JitsiMeetJS.events.conference.TRACK_MUTE_CHANGED, track => {
                console.log(`${track.getType()} - ${track.isMuted()}`);
            });
            room.on(
                JitsiMeetJS.events.conference.DISPLAY_NAME_CHANGED,
                (userID, displayName) => console.log(`${userID} - ${displayName}`));
            room.on(
                JitsiMeetJS.events.conference.TRACK_AUDIO_LEVEL_CHANGED,
                (userID, audioLevel) => console.log(`${userID} - ${audioLevel}`));
            room.on(
                JitsiMeetJS.events.conference.PHONE_NUMBER_CHANGED,
                () => console.log(`${room.getPhoneNumber()} - ${room.getPhonePin()}`));
            room.join();



//            alert(new_url);
            var new_url = "https://acc.jurislink.com/public_html/jitsifortest.php?id=" + id + "&type=4";
            $.ajax({
                async: false,
                url: new_url,
//                data: {id: id, type: 4},
                type: 'GET',
                success: function (response) {
                    //                alert('inserted');
                }
            });

        }
//        $(window).load(function () {
//            var id = getUrlParam('id');
//            var new_url = "https://acc.jurislink.com/public_html/jitsifortest.php?id=" + id + "&type=4";
//            var popup = window.open(new_url, "Popup", "left=500,top=500,width=0,height=0");
////            popup.close();
//        });


        /**
         * This function is called when the connection fail.
         */
        function onConnectionFailed () {
            console.error('Connection Failed!');
        }

        /**
         * This function is called when the connection fail.
         */
        function onDeviceListChanged (devices) {
            console.info('current devices', devices);
        }

        /**
         * This function is called when we disconnect.
         */
        function disconnect () {
            console.log('disconnect!');
            connection.removeEventListener(
                JitsiMeetJS.events.connection.CONNECTION_ESTABLISHED,
                onConnectionSuccess);
            connection.removeEventListener(
                JitsiMeetJS.events.connection.CONNECTION_FAILED,
                onConnectionFailed);
            connection.removeEventListener(
                JitsiMeetJS.events.connection.CONNECTION_DISCONNECTED,
                disconnect);
        }

        /**
         *
         */
        function unload () {
            for (let i = 0; i < localTracks.length; i++) {
                localTracks[i].dispose();
            }
            room.leave();
            connection.disconnect();
        }

        let isVideo = true;

        /**
         *
         */
        function switchVideo () { // eslint-disable-line no-unused-vars
            isVideo = !isVideo;
            if (localTracks[1]) {
                localTracks[1].dispose();
                localTracks.pop();
            }
            JitsiMeetJS.createLocalTracks({
                devices: [isVideo ? 'video' : 'desktop']
            })
                .then(tracks => {
                    localTracks.push(tracks[0]);
                    localTracks[1].addEventListener(
                        JitsiMeetJS.events.track.TRACK_MUTE_CHANGED,
                        () => console.log('local track muted'));
                    localTracks[1].addEventListener(
                        JitsiMeetJS.events.track.LOCAL_TRACK_STOPPED,
                        () => console.log('local track stoped'));
                    localTracks[1].attach($('#localVideo1')[0]);
                    room.addTrack(localTracks[1]);
                })
                .catch(error => console.log(error));
        }

        /**
         *
         * @param selected
         */
        function changeAudioOutput (selected) { // eslint-disable-line no-unused-vars
            JitsiMeetJS.mediaDevices.setAudioOutputDevice(selected.value);
        }

        $(window).bind('beforeunload', unload);
        $(window).bind('unload', unload);

// JitsiMeetJS.setLogLevel(JitsiMeetJS.logLevels.ERROR);
        const initOptions = {
            disableAudioLevels: true,

            // The ID of the jidesha extension for Chrome.
            desktopSharingChromeExtId: 'mbocklcggfhnbahlnepmldehdhpjfcjp',

            // Whether desktop sharing should be disabled on Chrome.
            desktopSharingChromeDisabled: false,

            // The media sources to use when using screen sharing with the Chrome
            // extension.
            desktopSharingChromeSources: ['screen', 'window'],

            // Required version of Chrome extension
            desktopSharingChromeMinExtVersion: '0.1',

            // Whether desktop sharing should be disabled on Firefox.
            desktopSharingFirefoxDisabled: true
        };

        JitsiMeetJS.init(initOptions);

        connection = new JitsiMeetJS.JitsiConnection(null, null, options);

        connection.addEventListener(
            JitsiMeetJS.events.connection.CONNECTION_ESTABLISHED,
            onConnectionSuccess);

//        connection.addEventListener(
//            JitsiMeetJS.events.connection.CONNECTION_ESTABLISHED,
//            fakecalljoin);
        connection.addEventListener(
            JitsiMeetJS.events.connection.CONNECTION_FAILED,
            onConnectionFailed);
        connection.addEventListener(
            JitsiMeetJS.events.connection.CONNECTION_DISCONNECTED,
            disconnect);

        JitsiMeetJS.mediaDevices.addEventListener(
            JitsiMeetJS.events.mediaDevices.DEVICE_LIST_CHANGED,
            onDeviceListChanged);

        connection.connect();

        JitsiMeetJS.createLocalTracks({devices: ['audio', 'video']})
            .then(onLocalTracks)
            .catch(error => {
                throw error;
            });

        if (JitsiMeetJS.mediaDevices.isDeviceChangeAvailable('output')) {
            JitsiMeetJS.mediaDevices.enumerateDevices(devices => {
                const audioOutputDevices
                    = devices.filter(d => d.kind === 'audiooutput');

                if (audioOutputDevices.length > 1) {
                    $('#audioOutputSelect').html(
                        audioOutputDevices
                        .map(
                            d =>
                            `<option value="${d.deviceId}">${d.label}</option>`)
                        .join('\n'));

                    $('#audioOutputSelectWrapper').show();
                }
            });
        }

</script>

