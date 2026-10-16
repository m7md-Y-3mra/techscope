<?php
/**
 * Demo articles — AI and Hardware desks.
 *
 * @package TechScope
 */

return array(

	array(
		'slug'     => 'inference-cost-curve',
		'title'    => 'The inference cost curve is bending, but not where anyone expected',
		'category' => 'ai',
		'tags'     => array( 'inference', 'gpu', 'economics', 'serving' ),
		'excerpt'  => 'Per-token prices keep falling while total spend keeps climbing. The gap between those two facts is where the next decade of infrastructure gets decided.',
		'days_ago' => 1,
		'blocks'   => array(
			array( 'p' => 'For three years the industry told a simple story about inference: models would get cheaper to run, so the cost of serving them would fall. The first half of that sentence turned out to be true. The second half did not.' ),
			array( 'p' => 'Per-token prices at the large providers have dropped by roughly an order of magnitude since the first general-availability APIs. Over the same period, aggregate inference spend at companies that build on those APIs has risen, often sharply. Nothing about that is contradictory. It is simply what happens when a cost falls fast enough to unlock uses that were previously uneconomic.' ),
			array( 'h2' => 'Cheaper tokens, more tokens' ),
			array( 'p' => 'The pattern has a name in energy economics — the rebound effect — and the shape here is familiar. When a product-support workflow cost forty cents to run, teams used it for escalations only. At four cents, they ran it on every ticket. At four cents with a reasoning model that emits ten times as many tokens, they are back to forty cents, except now it runs on every ticket instead of a tenth of them.' ),
			array( 'quote' => 'The unit got cheaper. The bill got bigger. Both numbers are real, and finance teams are learning to hold them at the same time.' ),
			array( 'p' => 'This is why "cost per token" has quietly stopped being a useful planning metric. The teams with predictable infrastructure budgets have moved to cost per resolved task — a number that absorbs retries, tool calls, context re-reads and the long tail of requests that fail and get re-run with a bigger model.' ),
			array( 'h2' => 'Where the money actually goes' ),
			array( 'p' => 'Break down a production serving bill and the token price is rarely the largest line. Three other costs tend to dominate:' ),
			array(
				'list' => array(
					'<strong>Context re-transmission.</strong> Agents that re-send a growing transcript on every step pay for the same prefix repeatedly. Prefix caching helps, but only if the prefix is actually stable — and most prompt-assembly code reorders it.',
					'<strong>Idle accelerator time.</strong> Reserved capacity that sits at thirty percent utilisation costs the same as capacity at ninety. Batch schedulers that cannot mix workloads leave most of that on the floor.',
					'<strong>Failed and retried work.</strong> A request that times out at step nine of a twelve-step agent has already been paid for in full.',
				),
			),
			array( 'p' => 'None of these are model problems. They are serving-architecture problems, and they respond to ordinary engineering: stable prompt prefixes, continuous batching, aggressive caching at the retrieval layer, and routing that sends easy requests to small models without a human deciding case by case.' ),
			array( 'h2' => 'The routing question' ),
			array( 'p' => 'Routing is where the near-term savings sit, and also where most teams get it wrong. The naive approach — classify the request, pick a model — adds a model call to every request and introduces a second thing that can be wrong. The approaches that hold up in production are less elegant: run the small model first, check the result against a cheap verifier, escalate only on failure.' ),
			array( 'p' => 'That design has an unglamorous property that makes it work. It fails toward correctness rather than toward cost, which means the cost savings are a side effect of the quality check rather than a trade against it.' ),
			array( 'h2' => 'What bends next' ),
			array( 'p' => 'The curve that matters now is not price per token. It is tokens required per unit of useful work — and that one is going up, because reasoning models buy accuracy with length. Serving infrastructure built on the assumption of short completions is being rebuilt around long ones, and the teams that have already made that change are the ones whose bills look boring.' ),
		),
	),

	array(
		'slug'     => 'small-models-edge',
		'title'    => 'Small models found their niche, and it was not the one anybody pitched',
		'category' => 'ai',
		'tags'     => array( 'small-models', 'edge', 'quantization', 'latency' ),
		'excerpt'  => 'The on-device revolution arrived sideways: not as phone assistants, but as the quiet classification layer sitting in front of everything else.',
		'days_ago' => 3,
		'blocks'   => array(
			array( 'p' => 'The pitch for small models was always the phone. Run the assistant locally, keep the data on device, cut the round trip. Some of that happened. Most of the actual deployment went somewhere less photogenic: into the first stage of server-side pipelines, where a two-billion-parameter model decides what the expensive model never has to see.' ),
			array( 'h2' => 'Triage, not conversation' ),
			array( 'p' => 'The workloads that small models dominate share a shape. The input is short, the output is a label or a short span, the latency budget is tens of milliseconds, and the volume is enormous. Spam scoring. Intent classification. Retrieval re-ranking. PII detection before a payload leaves the building. Safety pre-filters. None of these are conversations, and none of them need a frontier model.' ),
			array( 'quote' => 'A model that handles eighty percent of traffic at one-fiftieth the cost does not have to be impressive. It has to be consistent.' ),
			array( 'p' => 'Consistency is the operative word. A classifier that is right ninety-four percent of the time with a well-calibrated confidence score is more useful than one that is right ninety-seven percent of the time and cannot tell you when it is guessing, because the first one can be wired to escalate and the second one cannot.' ),
			array( 'h2' => 'Quantisation stopped being a compromise' ),
			array( 'p' => 'Four-bit weight quantisation used to cost real accuracy. Modern quantisation-aware training and better outlier handling have narrowed that to the point where, for classification-shaped tasks, the degradation is inside the noise of the evaluation set.' ),
			array( 'p' => 'The practical effect is memory bandwidth. A quantised small model fits in cache hierarchies that a full-precision one does not, and on inference hardware the bottleneck has been bandwidth rather than arithmetic for years.' ),
			array( 'h2' => 'The deployment pattern' ),
			array( 'p' => 'The architecture that has settled out looks like this: a small model runs on the same host as the application, often on CPU, with no network call at all. It handles the easy majority. What it escalates goes to a mid-size model, and only what that cannot resolve reaches the frontier tier.' ),
			array(
				'list' => array(
					'Stage one: on-host small model, sub-10ms, no network.',
					'Stage two: regional mid-size model, tens of milliseconds.',
					'Stage three: frontier model, hundreds of milliseconds, used sparingly.',
				),
			),
			array( 'p' => 'Each stage has its own evaluation set and its own escalation threshold, tuned independently. The result is a system where the expensive component is load-bearing but rare — which is exactly how expensive components should be used.' ),
			array( 'h2' => 'What this costs you' ),
			array( 'p' => 'Three models in a pipeline means three deployment surfaces, three eval sets and three things that drift. Teams that have run this architecture for a year describe the ongoing work as distinctly unglamorous: mostly keeping the stage-one model current with the distribution of what people actually send, which changes faster than anyone budgets for.' ),
		),
	),

	array(
		'slug'     => 'training-data-provenance',
		'title'    => 'Provenance is becoming a build artefact, not a legal afterthought',
		'category' => 'ai',
		'tags'     => array( 'datasets', 'provenance', 'compliance', 'tooling' ),
		'excerpt'  => 'Dataset lineage used to be a spreadsheet someone updated before an audit. It is turning into a tracked, diffable, CI-enforced part of the training pipeline.',
		'days_ago' => 6,
		'blocks'   => array(
			array( 'p' => 'Ask a training team two years ago where a particular corpus came from and you would get a Slack archaeology expedition. Ask the same question now at a shop that ships models commercially and you are increasingly likely to get a manifest hash, a license classification and a commit that added it.' ),
			array( 'h2' => 'What changed' ),
			array( 'p' => 'Partly procurement. Enterprise buyers started asking provenance questions in security reviews, and "we are confident it is fine" stopped clearing them. Partly engineering: once datasets are versioned properly, a whole class of reproducibility bugs becomes tractable, which is reason enough on its own.' ),
			array( 'quote' => 'The compliance requirement paid for the tooling. The engineering benefit is why nobody wants to give it back.' ),
			array( 'h2' => 'The shape of a manifest' ),
			array( 'p' => 'A dataset manifest that actually works is boring. It records, for each shard: the source URI, a content hash, the fetch timestamp, a license identifier, the filters applied, and the hash of the filter code that applied them.' ),
			array(
				'code' => "shard: cc-2026-14/0042\nsource: https://example.org/dump/2026-14\nsha256: 7f3c…a19b\nfetched: 2026-04-02T11:04:00Z\nlicense: CC-BY-4.0\nfilters:\n  - dedupe@2.1.0  (sha256: 1c9e…)\n  - lang-id@0.8.3 (sha256: b40f…)\n  - pii-strip@3.0.1 (sha256: 55a2…)",
			),
			array( 'p' => 'The filter hashes are the part teams skip and then regret. Without them, a reproducible build is not reproducible — the same source with a changed deduplication threshold produces a different corpus, and six months later nobody remembers the threshold moved.' ),
			array( 'h2' => 'Enforcement in CI' ),
			array( 'p' => 'The manifests only hold if something checks them. The pattern that works: a pre-training job that refuses to start if any shard lacks a license classification, and a nightly job that re-resolves source URIs and flags anything whose content hash has drifted.' ),
			array( 'p' => 'Drift is more common than people expect. Sources get re-published, licenses get changed retroactively, and a corpus assembled in March can be materially different if reassembled in September.' ),
			array( 'h2' => 'The unresolved part' ),
			array( 'p' => 'Provenance tracking tells you where data came from. It does not tell you whether you were entitled to it, and the entitlement question is still being litigated in several jurisdictions at once. What the tooling buys is optionality: a team that can enumerate its corpus precisely can remove a slice and retrain. A team that cannot has only one move available, and it is expensive.' ),
		),
	),

	array(
		'slug'     => 'chiplet-interconnect',
		'title'    => 'The chiplet era moved the hard problem from the die to the gap between dies',
		'category' => 'hardware',
		'tags'     => array( 'chiplets', 'packaging', 'interconnect', 'silicon' ),
		'excerpt'  => 'Disaggregating a processor solved a yield problem and created a physics problem. The industry is now spending its best engineering on millimetres of substrate.',
		'days_ago' => 2,
		'blocks'   => array(
			array( 'p' => 'Chiplets were adopted for an unromantic reason: large dies yield badly, and yield loss scales with area. Cutting a processor into several smaller dies and assembling them in a package recovers most of that loss. The economics were compelling enough that the entire industry moved within a few product generations.' ),
			array( 'h2' => 'The cost of the cut' ),
			array( 'p' => 'Every cut becomes a wire. A signal that crossed a monolithic die at full clock now crosses a package boundary, which costs picoseconds and, more importantly, picojoules. On-die communication is close to free by comparison. Die-to-die links are not.' ),
			array( 'quote' => 'A modern accelerator spends a meaningful fraction of its power budget moving bits between its own pieces. That number used to be rounding error.' ),
			array( 'p' => 'This is why advanced packaging — silicon interposers, bridges, hybrid bonding — has become the real differentiator. Shortening and widening the die-to-die path is the single highest-leverage change available, and it happens in the substrate rather than the transistor.' ),
			array( 'h2' => 'Standards, slowly' ),
			array( 'p' => 'UCIe gave the industry a common die-to-die interface, which matters less for mixing vendors than people initially claimed and more for internal reuse. A company with four chiplet designs and one standard interface can recombine them without redesigning the link each time.' ),
			array(
				'list' => array(
					'<strong>Standard package:</strong> organic substrate, longer reach, lower density, cheaper.',
					'<strong>Advanced package:</strong> interposer or bridge, short reach, very high density, expensive and capacity-constrained.',
					'<strong>Hybrid bonding:</strong> direct copper-to-copper, shortest reach, highest density, hardest to manufacture.',
				),
			),
			array( 'h2' => 'Thermal reality' ),
			array( 'p' => 'Stacking dies concentrates heat. A logic die under a memory stack has a thermal path that runs through the memory, and memory has a much narrower temperature tolerance than logic does. The result is designs that are thermally limited before they are electrically limited — a constraint that has pushed liquid cooling from exotic to routine in dense deployments.' ),
			array( 'h2' => 'What this means downstream' ),
			array( 'p' => 'For software, the practical consequence is that memory locality now has a package-level dimension. Which chiplet a thread runs on, and which memory stack it touches, has measurable performance consequences. The scheduling work this implies is largely still ahead of the industry, and it looks a great deal like NUMA tuning did two decades ago.' ),
		),
	),

	array(
		'slug'     => 'datacentre-power-wall',
		'title'    => 'The power wall is a grid interconnection queue',
		'category' => 'hardware',
		'tags'     => array( 'datacentre', 'power', 'grid', 'cooling' ),
		'excerpt'  => 'Compute capacity is no longer limited by how fast racks can be built. It is limited by how long it takes to get the substation energised.',
		'days_ago' => 5,
		'blocks'   => array(
			array( 'p' => 'Ask an operator what is slowing down capacity expansion and the answer has shifted. It used to be chip supply. Then it was construction labour. Now, consistently, it is grid interconnection — the queue to connect a new large load to the transmission network.' ),
			array( 'h2' => 'Queues measured in years' ),
			array( 'p' => 'Interconnection studies for large loads routinely run multiple years in the major markets. The study itself is not the bottleneck; the sequencing is. Each new request has to be evaluated against every other pending request, and the queue is long.' ),
			array( 'quote' => 'A rack can be racked in a week. The megawatts behind it were requested three years ago.' ),
			array( 'p' => 'The consequence is that site selection has inverted. Operators used to choose a location and then arrange power. They now find power and then decide what to build there, which is why capacity is appearing in places with no particular network advantage but an available substation.' ),
			array( 'h2' => 'Behind-the-meter answers' ),
			array( 'p' => 'The workarounds are getting serious. On-site generation, previously a backup concern, is being designed as primary capacity in some builds. Co-location with existing large industrial loads that have spare headroom. Long-term contracts with generators that include firm delivery rather than financial settlement.' ),
			array(
				'list' => array(
					'Behind-the-meter generation, sized for primary load rather than backup.',
					'Load flexibility commitments — curtailing training jobs during grid stress in exchange for faster interconnection.',
					'Siting next to retiring thermal plants, where transmission capacity already exists.',
				),
			),
			array( 'h2' => 'Flexibility as a bargaining chip' ),
			array( 'p' => 'The second item is the most interesting technically. Training workloads are unusually interruptible compared to most industrial load — a checkpointed job can pause for an hour at modest cost. Operators that can commit to curtailment get treated differently in the queue, because a flexible load is a smaller planning problem than a firm one.' ),
			array( 'p' => 'Making that commitment real requires scheduling infrastructure that most organisations do not have: the ability to drain a cluster predictably, checkpoint cleanly and resume without a human in the loop. The teams building it describe it as the least glamorous and most valuable work on their roadmap.' ),
		),
	),

	array(
		'slug'     => 'risc-v-server-silicon',
		'title'    => 'RISC-V in the server room is a software story with a hardware deadline',
		'category' => 'hardware',
		'tags'     => array( 'risc-v', 'isa', 'servers', 'toolchains' ),
		'excerpt'  => 'The silicon is arriving on schedule. Whether it matters depends on a long tail of build systems that nobody owns.',
		'days_ago' => 9,
		'blocks'   => array(
			array( 'p' => 'Server-class RISC-V parts have moved from announcement to availability. The cores are competitive on paper, the vector extension is ratified and implemented, and the platform specifications that make a machine bootable without vendor-specific hacks are in place.' ),
			array( 'h2' => 'The ratification backlog cleared' ),
			array( 'p' => 'The extensions that server workloads need — vector, bit manipulation, cryptography, hypervisor, and the profile specifications that bundle them into something an OS can target — are done. That removes the objection that dominated the conversation for years, which was that RISC-V was a specification in motion.' ),
			array( 'quote' => 'The architecture stopped being the risk. The build infrastructure became the risk.' ),
			array( 'h2' => 'Where it actually breaks' ),
			array( 'p' => 'Porting a modern server stack to a new architecture is rarely about the compiler. GCC and LLVM both target RISC-V well. The breakage lives in the layers above:' ),
			array(
				'list' => array(
					'Hand-written assembly in cryptographic and compression libraries, present for performance and usually architecture-specific.',
					'CI matrices that have no RISC-V runner, so regressions land unnoticed.',
					'Container images whose base layers are not published for the architecture, which silently blocks everything downstream.',
					'Build scripts with hardcoded architecture checks that predate the question being interesting.',
				),
			),
			array( 'p' => 'None of these are hard problems individually. Collectively they are thousands of small patches across projects with varying maintenance capacity, and the work is unevenly distributed — the popular projects get attention, the load-bearing-but-obscure ones do not.' ),
			array( 'h2' => 'The runner problem' ),
			array( 'p' => 'The highest-leverage intervention is boring: make RISC-V runners cheaply available in mainstream CI services. A project with a runner will keep its port working almost automatically, because breakage shows up as a red build. A project without one will regress between releases no matter how good the initial port was.' ),
			array( 'h2' => 'Timing' ),
			array( 'p' => 'The deadline is set by procurement rather than engineering. Several large buyers have signalled architecture diversity requirements in forward contracts. Whether the software ecosystem is ready when those contracts are executed is the open question, and it will be decided by maintainer-hours rather than transistors.' ),
		),
	),

);
