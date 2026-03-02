<?php

	function render_skema($id) {
		if (strlen(file_get_contents('https://personligtskema.ku.dk/ical.asp?objectclass=student&id=' . $id)) > 600) {
			$skema_json = file_get_contents('https://personligtskema.ku.dk/PersSkema.ashx?id=' . $id . '&outputType=JSON&objectclass=student');
			$events = json_decode($skema_json, true);

			if (!is_array($events) || count($events) === 0) {
				return false;
			}

			usort($events, function($a, $b) {
				$at = strtotime(($a['b'] ?? '') . ' ' . ($a['c'] ?? '00:00'));
				$bt = strtotime(($b['b'] ?? '') . ' ' . ($b['c'] ?? '00:00'));
				return $at <=> $bt;
			});
			$output = "";
			$days = ['søn', 'man', 'tir', 'ons', 'tor', 'fre', 'lør'];
			$currentWeek = (int)date('W');
			$weeks_rendered = [];

			$output .= '<h1 class="sau_title bar_top centered raised">Skema for ' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '</h1>';
			$output .= "<a href='javascript:logud()' id='logout_btn'>Yo mate, I'm out!</a>";
			$output .= "<a href='https://www.facebook.com/saushoppen/' target='_blank' id='help_btn'>Help!</a><br /><br />";
			$output .= '<div id="week_list" class="bar_bottom raised">Uger: ';

			$old_week = 0;
			$new_week = 0;
          
            $isSpring = (int)date('W') >= 5 && (int)date('W') <= 30;

			for ($i = 0; $i < count($events); $i++) {
				$ev = $events[$i];
				$ev_date = $ev['b'] ?? '';
				$ev_start = $ev['c'] ?? '';
				$ev_end = $ev['d'] ?? '';
				$ev_title = $ev['e'] ?? '';
				$ev_code = $ev['f'] ?? '';
				$ev_desc = $ev['g'] ?? '';
				$ev_type = $ev['h'] ?? '';
				$ev_staff = $ev['i'] ?? '';
				$ev_loc = $ev['j'] ?? '';
				$ev_group = $ev['k'] ?? '';
				$event = "";

				$start_ts = strtotime($ev_date . ' ' . $ev_start);
				$end_ts = strtotime($ev_date . ' ' . $ev_end);
				$new_week = (int)date('W', $start_ts);

				if ($i == 0) {
					$o_ss = 0;
					for ($j = 0; $j < count($events); $j++) {
						$e_ss = strtotime(($events[$j]['b'] ?? '') . ' ' . ($events[$j]['c'] ?? '00:00'));
						$w_ss = (int)date('W', $e_ss);
                      
                        if ($isSpring && ($w_ss <5 || $w_ss >30)) { continue; }
                      
						if ($o_ss != $w_ss) {
							$output .= '<a class="sau_list" href="#sau_week_' . $w_ss . '">' . $w_ss . '</a>';
							$weeks_rendered[] = $w_ss;
						}
						$o_ss = $w_ss;
					}
					$output .= '<div id="me"><a href="https://www.facebook.com/davidsvane" target="_blank">David Svane (Opdateret af Rasmus Skousen)</a> </div></div>';
                  
                    if ($isSpring && ($new_week <5 || $new_week >30)) { continue; }
                  
					$output .= '<h2 class="sau_week centered" id="sau_week_' . (int)$new_week . '">Uge ' . (int)$new_week . '</h2>';
                    $output .= '<table class="personligt_skema" cellspacing="0" cellpadding="0">';
					$output .= '<tr class="ps_titles"><td class="date">Dato</td><td class="start" colspan="1">Tid</td><td>Aktivitet</td><td class="loc">Lokale</td></tr>';
				} else if ($old_week != $new_week) {
                  
                    if ($isSpring && ($new_week <5 || $new_week >30)) { continue; }
                  
					$output .= '</table>';
					$output .= '<h2 class="sau_week centered" id="sau_week_' . (int)$new_week . '">Uge ' . (int)$new_week . '</h2>';
					$output .= '<table class="personligt_skema" cellspacing="0" cellpadding="0">';
					$output .= '<tr class="ps_titles"><td class="date">Dato</td><td class="start" colspan="1">Tid</td><td>Aktivitet</td><td class="loc">Lokale</td></tr>';
				}
				$old_week = $new_week;
              
                if ($isSpring && ($new_week <5 || $new_week >30)) { continue; }

				$event .= '<tr id="e_' . $i . '" class="not_title row_' . ($i%2==0?'odd':'even') . '">';
				$event .= '<td class="date">' . $days[(int)date('w', $start_ts)]  . ' ' . date('d/m', $start_ts) . '</td>';
				$event .= '<td class="start">' . date('H:i', $start_ts) . ' - ' . date('H:i', $end_ts) . '</td>';
				$event .= '<td class="code">' . $ev_code . '</td>';
				$event .= '<td class="loc">' . $ev_loc . '</td>';
				$event .= '</tr>';

				$event .= '<tr id="e_' . $i . '_more" class="row_more row_' . ($i%2==0?'odd':'even') . '">';
				$event .= '<td class="summary" colspan="4">' . $ev_title . ' - ';
				$event .= $ev_group . ' - ';
				$event .= $ev_desc . ' - ';
				$event .= $ev_staff . ' - ' . $ev_type . '</td>';
				$event .= '</tr>';

				$output .= $event;

				if ($i == count($events)-1) {
					$output .= '</table>';
				}
			}

			if (in_array($currentWeek, $weeks_rendered)) {
				$output .= '<script>document.addEventListener("DOMContentLoaded", function(){ var el = document.getElementById("sau_week_' . $currentWeek . '"); if (el) { el.scrollIntoView({behavior:"auto", block:"start"}); window.location.hash = "sau_week_' . $currentWeek . '"; }});</script>';
			}

			$output .= '<br /><br /><br />';
			return $output;
		} else {
			return false;
		}
	}
?>
