<div class="panel panel-default">
    <div id="ticket-body" class="panel-body">        
		<div class="row" style="margin-bottom: 0.2em;">
			<div class="col-sm-8 col-lg-7">				
				<h2 style="margin: 0em 0em 0.5em 0em;">
				@if ($ticket->completed_at)
					<span class="text-success"><span class="glyphicon glyphicon-ok-circle" title="tiquet completat" style="cursor: help"></span> {{ $ticket->subject }}</span>
				@else
					<span class="text-warning"><span class="glyphicon glyphicon-file" title="tiquet obert" style="cursor: help"></span> {{ $ticket->subject }}</span>
				@endif
				</h2>
			</div>
			<div class="col-sm-4 col-lg-5 text-right">
				@if ($u->currentLevel() > 1)
					<a href="{{ route($setting->grab('main_route').'.hide', ['value' => $ticket->hidden ? 'false' : 'true', 'ticket'=>$ticket->id]) }}" class="btn btn-default tooltip-info" style="border: none; color: #aaa;" data-toggle="tooltip" data-placement="auto top" title="{{ trans('panichd::lang.ticket-hidden-button-title') }}">{!! $ticket->hidden ? '<span class="glyphicon glyphicon-eye-close"></span> '.trans('panichd::lang.ticket-hidden') : '<span class="glyphicon glyphicon-eye-open"></span> '.trans('panichd::lang.ticket-visible') !!}</a>
				@endif
				@if ($ticket->updated_at!=$ticket->created_at)
					<span class="tooltip-info" data-toggle="tooltip" data-placement="auto top" title="{{ trans('panichd::lang.updated-date', [
						'date' => \Carbon\Carbon::parse($ticket->updated_at)->format(trans('panichd::lang.datetime-format'))
					]) }}" style="display: inline-block; color: #aaa; cursor: help">
						<span class="glyphicon glyphicon-pencil"></span> {{ $ticket->updated_at->diffForHumans() }}
					</span>
				@endif
				<span class="tooltip-info" data-toggle="tooltip" data-placement="auto top" title="{{ trans('panichd::lang.creation-date', [
						'date' => \Carbon\Carbon::parse($ticket->created_at)->format(trans('panichd::lang.datetime-format'))
					]) }}" style="display: inline-block; color: #aaa; cursor: help">
					<span class="glyphicon glyphicon-certificate"></span> {{ $ticket->created_at->diffForHumans() }}
				</span>&nbsp;
								
				@if($u->isAdmin())
					@if($setting->grab('delete_modal_type') == 'builtin')
						{!! link_to_route(
										$setting->grab('main_route').'.destroy', trans('panichd::lang.btn-delete'), $ticket->id,
										[
										'class' => 'btn btn-default deleteit',
										'form' => "delete-ticket-$ticket->id",
										"node" => $ticket->subject
										])
						!!}
					@elseif($setting->grab('delete_modal_type') == 'modal')
					{{-- // OR; Modal Window: 1/2 --}}
						{!! CollectiveForm::open(array(
								'route' => array($setting->grab('main_route').'.destroy', $ticket->id),
								'method' => 'delete',
								'style' => 'display:inline'
						   ))
						!!}
						<button type="button"
								class="btn btn-default"
								data-toggle="modal"
								data-target="#confirmDelete"
								data-title="{!! trans('panichd::lang.show-ticket-modal-delete-title', ['id' => $ticket->id]) !!}"
								data-message="{!! trans('panichd::lang.show-ticket-modal-delete-message', ['subject' => $ticket->subject]) !!}"
						 >
						  {{ trans('panichd::lang.btn-delete') }}
						</button>
					@endif
						{!! CollectiveForm::close() !!}
					{{-- // END Modal Window: 1/2 --}}
				@endif
			</div>
		</div>

		<div class="row">
			<div class="col-lg-3 col-sm-4">				
				<p>
				<strong>{{ trans('panichd::lang.ticket') }}</strong>{{ trans('panichd::lang.colon') . trans('panichd::lang.table-id') . $ticket->id }}
				@if ($u->currentLevel() > 1)
					@if ($ticket->user_id != $ticket->creator_id)
						<br /><strong>{{ trans('panichd::lang.show-ticket-creator') }}</strong>{{ trans('panichd::lang.colon') . $ticket->creator->name }}<br />
					@endif
					
					<br /><strong>{{ trans('panichd::lang.owner') }}</strong>{{ trans('panichd::lang.colon') }} 

					@if ($setting->grab('user_route') != 'disabled')
						<a href="{{ route($setting->grab('user_route'), ['id'=> $ticket->user_id]) }}">
					@endif
					
					@if ($ticket->deleted_owner)
						<span class="tooltip-info" data-toggle="tooltip" data-placement="auto bottom" title="{{ trans('panichd::lang.deleted-member') }}">{!! $ticket->owner_name !!}</span>
					@elseif ($ticket->owner_email != "")
						<span class="tooltip-info" data-toggle="tooltip" data-placement="auto bottom" title="{{ $ticket->owner_email }}">{!! $ticket->owner_name !!} <span class="glyphicon glyphicon-question-sign"></span></span>
					@else
						{!! $ticket->owner_name !!}
					@endif
					
					@if ($setting->grab('user_route') != 'disabled')
						</a>
					@endif
					
					@if ($ticket->deleted_owner)
						<br /><span class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> {{ trans('panichd::lang.ticket-owner-deleted-warning') }}</span>
					@endif
					
					@if ($ticket->owner_email == "")
						<br /><span class="text-warning"><span class="glyphicon glyphicon-warning-sign"></span> {{ trans('panichd::lang.ticket-owner-no-email-warning') }}</span>
					@endif
					
					@if ($setting->grab('departments_feature') && $ticket->owner->department)
						@if ($ticket->owner->department->ancestor || $ticket->owner->department->is_main())
							<br /><strong>{{ trans('panichd::lang.department') }}</strong>{{ trans('panichd::lang.colon') . ($ticket->owner->department->is_main() ? $ticket->owner->department->getName() : $ticket->owner->department->ancestor->getName()) }}
						@endif
						@if (!$ticket->owner->department->is_main())
							<br /><strong>{{ trans('panichd::lang.dept-descendant') }}</strong>{{ trans('panichd::lang.colon') . $ticket->owner->department->getName() }}
						@endif
					@endif
				@endif
				
				<br /><strong>{{ trans('panichd::lang.status') }}</strong>{{ trans('panichd::lang.colon') }}
				@if( $ticket->isComplete() && ! $setting->grab('default_close_status_id') )
					<span style="color: blue">Complete</span>
				@else
					<span style="color: {{ $ticket->status->color }}">{{ $ticket->status->name }}</span>
				@endif
				
				@php
					\Carbon\Carbon::setLocale(config('app.locale'));
				@endphp
				
				@if ($u->currentLevel() > 1)
					<br /><strong>{{ trans('panichd::lang.priority') }}</strong>{{ trans('panichd::lang.colon') }}
					<span style="color: {{ $ticket->priority->color }}">
						{{ $ticket->priority->name }}
					</span>
					
					<br />
					@if ($ticket->isComplete())
						<strong>{{ trans('panichd::lang.start-date') }}</strong>{{ trans('panichd::lang.colon') }}{!! $ticket->getDateForHumans('start_date') !!}
						<br /><strong>{{ trans('panichd::lang.limit-date') }}</strong>{{ trans('panichd::lang.colon') }}
						@if ($ticket->limit_date == "")
							{{ trans('panichd::lang.no') }}
						@else
							{!! $ticket->getDateForHumans('limit_date') !!}
						@endif
						<br /><strong>{{ trans('panichd::lang.table-completed_at') }}</strong>{{ trans('panichd::lang.colon') }}{!! $ticket->getDateForHumans('completed_at') !!}
					@else
						<strong>{{ trans('panichd::lang.table-calendar') }}</strong>{{ trans('panichd::lang.colon') }}{!! $ticket->getCalendarInfo(true) !!}
					@endif
					
					</p><p>					
				@else
					<br />
				@endif
				
				<strong>{{ trans('panichd::lang.category') }}</strong>{{ trans('panichd::lang.colon') }}
				<span style="color: {{ $ticket->category->color }}">
					{{ $ticket->category->name }}
				</span>
				
				@if ($u->currentLevel() > 1)
					<br /><strong>{{ trans('panichd::lang.responsible') }}</strong>{{ trans('panichd::lang.colon') }}{{ $ticket->agent->name }}
				@endif
								
				@if ($ticket->has('tags') && ($u->currentLevel() > 1 || in_array($ticket->user_id, $u->getMyNoticesUsers())) )
					<br /><strong>{{ trans('panichd::lang.tags') }}</strong>{{ trans('panichd::lang.colon') }}
					@foreach ($ticket->tags as $i=>$tag)
						<button class="btn btn-default btn-xs" style="pointer-events: none; border: none; color: {{$tag->text_color}}; background: {{$tag->bg_color}}">{{$tag->name}}</button>
					@endforeach					
				@endif
				</p>				
			</div>
			<div class="col-lg-9 col-sm-8">
				<div class="row row-eq-height">
					<div class="description-col {{ $ticket->intervention_html ? 'col-lg-6' : 'col-md-12'}}">
						<div>
							<b>{{ trans('panichd::lang.description') }}</b>
						</div>
						<div class="summernote-text-wrapper"> {!! $ticket->html !!} </div>
					</div>
					@if ($ticket->intervention_html)
						<div class="intervention-col col-lg-6">
							<div>
								<b>{{ trans('panichd::lang.intervention') }}</b>
							</div>
							<div class="summernote-text-wrapper"> {!! $ticket->intervention_html !!} </div>
						</div>
					@endif					
				</div>
				
				@if($setting->grab('ticket_attachments_feature') && $ticket->attachments->count() > 0)
					<div class="row row-ticket-attachments" style="">
					<?php
						$images_count = $ticket->attachments()->images()->count();
						$notimages_count = $ticket->attachments()->notImages()->count();
					?>
						
					@if($images_count > 0)
						<div class="{{ $ticket->attachments()->notImages()->count() > 0 ? 'col-sm-6' : 'col-xs-12' }}">
							<div class="row">
							<div class="col-xs-12"><b style="display: block; margin: 0em 0em 0.5em 0em;">{{ trans('panichd::lang.attached-images') }}</b></div>
							<div class="col-xs-12">
								<div id="ticket_attached" class="panel-group grouped_check_list deletion_list attached_list">
														
								@foreach($ticket->attachments()->images()->get() as $attachment)							
									@include('panichd::tickets.partials.attachment_image')
								@endforeach
								</div>
							</div>
							</div>
						</div>
					@endif
					@if($notimages_count > 0)
						<div class="{{ $ticket->attachments()->images()->count() > 0 ? 'col-sm-6' : 'col-xs-12' }}">
							<div class="row">
							<div class="col-xs-12"><b style="display: block; margin: 0em 0em 0.5em 0em;">{{ trans('panichd::lang.attached-files') }}</b></div>
							<div class="col-xs-12">
								<div id="ticket_attached" class="row panel-group attached_list">
														
								@foreach($ticket->attachments()->notImages()->get() as $attachment)									
									<div class="{{ $images_count > 0 ? 'col-xs-12' : 'col-lg-3 col-md-4 col-sm-6' }}" style="margin-bottom: {{ $images_count > 0 ? '10px' : '15px' }}">
										@include('panichd::tickets.partials.attachment', ['template'=>'view'])
									</div>
								@endforeach
								</div>
							</div>
							</div>
						</div>
					@endif						
					</div>					
				@endif
			</div>
		</div>
		
		<div style="margin: 1em 0em 0em 0em;">
			@if(! $ticket->completed_at && $close_perm == 'yes')			
				<button type="submit" class="btn btn-default" data-toggle="modal" data-target="#ticket-complete-modal" data-status_id="{{ $setting->grab('default_close_status_id') }}">{{ trans('panichd::lang.btn-mark-complete') }}</button>						
			@elseif($ticket->completed_at && $reopen_perm == 'yes')
				{!! link_to_route($setting->grab('main_route').'.reopen', trans('panichd::lang.reopen-ticket'), $ticket->id,
										['class' => 'btn btn-default']) !!}
			@endif
			@if($u->currentLevel() > 1 && $u->canManageTicket($ticket->id))
				{!! link_to_route($setting->grab('main_route').'.edit', trans('panichd::lang.btn-edit'), $ticket->id,[
					'class' => 'btn btn-default',
					'style' => 'margin: 0em 0em 0em 0.5em'
				]) !!}
				<div class="visible-xs"><br /></div>
			@endif
		</div>
        
        {!! CollectiveForm::open([
                        'method' => 'DELETE',
                        'route' => [
                                    $setting->grab('main_route').'.destroy',
                                    $ticket->id
                                    ],
                        'id' => "delete-ticket-$ticket->id"
                        ])
        !!}
        {!! CollectiveForm::close() !!}
    </div>
</div>