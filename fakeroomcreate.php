<!--<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="https://esolz.co.in/lab3/jitsitest/libs/strophe/strophe.js"></script>
<script src="https://esolz.co.in/lab3/jitsitest/libs/strophe/strophe.disco.min.js?v=1"></script>
<script src="https://meet.jit.si/libs/lib-jitsi-meet.min.js"></script>-->

<script src="https://code.jquery.com/jquery-2.1.1.min.js"></script>
<script src="https://esolz.co.in/lab3/jitsitest/libs/strophe/strophe.js"></script>
<script src="https://esolz.co.in/lab3/jitsitest/libs/strophe/strophe.disco.min.js?v=1"></script>
<script src="https://meet.jit.si/libs/lib-jitsi-meet.min.js"></script>
<script>

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

        var id =<?php echo $_REQUEST[ 'id' ]; ?>;


//        var id = getUrlParam('id');
        //        alert(typeof id);

        function onConnectionSuccess () {
            room = connection.initJitsiConference(id, confOptions);
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


        }
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