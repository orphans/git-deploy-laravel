<div style="font-family:sans-serif; font-size:11pt;">
	<h1>{{ $git['repository']['name'] }} updated</h1>
	<p>The Git repository has just been updated by <em>{{ $git['user_name'] }}</em> via the web hook.</p>
	<table cellspacing="0" cellpadding="10" width="100%" style="background:#F6F6F6; border:1px solid #EEEEEE; font-size:11pt; margin-bottom: 0.5em;">
		<tr>
			<td valign="top" align="right"><strong>System user</strong></td>
			<td valign="top">{{ $server['user'] }}</td>
		</tr>
		<tr>
			<td valign="top" align="right"><strong>Command</strong></td>
			<td valign="top">{{ $server['cmd'] }}</td>
		</tr>
		<tr>
			<td valign="top" align="right"><strong>Response</strong></td>
			<td valign="top">{{ $server['response'] }}</td>
		</tr>
	</table>
	<table cellspacing="0" cellpadding="10" width="100%" style="background:#F6F6F6; border:1px solid #EEEEEE; font-size:11pt;">
		<tr>
			<td valign="top" align="right"><strong>Repository</strong></td>
			<td valign="top">{{ $git['repository']['name'] }}<br><em style="font-size:80%;">{{ $git['repository']['description'] }}</em></td>
		</tr>
		<tr>
			<td valign="top" align="right"><strong>Ref</strong></td>
			<td valign="top">{{ $git['ref'] }}</td>
		</tr>
		<tr>
			<td valign="top" align="right"><strong>Before</strong></td>
			<td valign="top"><code style="font-family:monospace; padding:2px; border:1px solid #CCCCCC; background:#EEEEEE; border-radius:4px;">{{ $git['before'] }}</code></td>
		</tr>
		<tr>
			<td valign="top" align="right"><strong>After</strong></td>
			<td valign="top"><code style="font-family:monospace; padding:2px; border:1px solid #CCCCCC; background:#EEEEEE; border-radius:4px;">{{ $git['after'] }}</code></td>
		</tr>
		<tr>
			<td valign="top" align="right"><strong>URL</strong></td>
			<td valign="top">{{ $git['repository']['url'] }}</td>
		</tr>
		<tr>
			<td valign="top" align="right"><strong>Homepage</strong></td>
			<td valign="top"><a href="{{ $git['repository']['homepage'] }}">{{ $git['repository']['homepage'] }}</a></td>
		</tr>
	</table>
	<h2>Commit log</h2>
	<table cellspacing="0" cellpadding="10" width="100%" style="font-size:9pt;">
		<thead>
			<tr style="background:#BBBBBB;">
				<th valign="middle" align="left">Date</th>
				<th valign="middle" align="left">Subject</th>
				<th valign="middle" align="left">Author</th>
				<th valign="middle" align="left">Commit</th>
			</tr>
		</thead>
		@foreach($git['commits'] as $commit)
		<tbody>
			<tr>
				<td valign="middle" align="left" style="border-bottom:1px solid #BBBBBB;">{{ $commit['human_date'] }}</td>
				<td valign="middle" align="left" style="border-bottom:1px solid #BBBBBB;">{{ $commit['human_subject'] }}</td>
				<td valign="middle" align="left" style="border-bottom:1px solid #BBBBBB;"><a href="mailto:{{ $commit['author']['email'] }}">{{ $commit['author']['name'] }}</a></td>
				<td valign="middle" align="left" style="border-bottom:1px solid #BBBBBB;"><a href="{{ $commit['url'] }}" title="{{ $commit['id'] }}"><code style="font-family:monospace; padding:2px; border:1px solid #CCCCCC; background:#EEEEEE; border-radius:4px;">{{ $commit['human_id'] }}</code></a></td>
			</tr>
		</tbody>
		@endforeach
	</table>
</div>
