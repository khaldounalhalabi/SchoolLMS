@php
    $level       = $node['level'];
    $circleClass = $level->color();
    $label       = $level->label($node['mastery_percent']);
    $hasChildren = count($node['children']) > 0;
    $pct         = $node['mastery_percent'] !== null ? max(0, min(100, (float) $node['mastery_percent'])) : null;
    $arcDeg      = $pct !== null ? round($pct * 3.6) : 0;
@endphp
<li class="tree-node">
    <div class="node-row" @if($hasChildren) data-node-id="{{ $node['id'] }}" @endif>
        @if($hasChildren)
            <span class="node-toggle" id="toggle-{{ $node['id'] }}">▶</span>
        @else
            <span style="width:16px;"></span>
        @endif
        <div class="node-gauge {{ $circleClass }}" @if($pct !== null) style="--arc: {{ $arcDeg }}deg" @endif>
            <span class="node-gauge-label">{{ $label }}</span>
        </div>
        <div>
            <div class="node-name">{{ $node['name'] }}</div>
            @if($node['description'])
                <div class="node-desc">{{ $node['description'] }}</div>
            @endif
        </div>
    </div>
    @if($hasChildren)
        <div class="node-children" id="children-{{ $node['id'] }}">
            <ul>
                @foreach($node['children'] as $child)
                    @include('components.diagnostic.tree-node', ['node' => $child])
                @endforeach
            </ul>
        </div>
    @endif
</li>
