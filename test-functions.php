<?php

namespace EventSauce\BackOff {
    function usleep(int $duration): void {
        SleepSpy::sleep($duration);
    }
}
