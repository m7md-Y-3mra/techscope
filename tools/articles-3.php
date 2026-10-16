<?php
/**
 * Demo articles — Science and Policy desks.
 *
 * @package TechScope
 */

return array(

	array(
		'slug'     => 'quantum-error-correction',
		'title'    => 'Error correction turned quantum computing into an engineering schedule',
		'category' => 'science',
		'tags'     => array( 'quantum', 'error-correction', 'qubits', 'research' ),
		'excerpt'  => 'Below-threshold logical qubits changed the question from whether the approach works to how many physical qubits the useful version needs.',
		'days_ago' => 10,
		'blocks'   => array(
			array( 'p' => 'For most of its history, quantum computing was a physics question. Demonstrations that a logical qubit can be made to have a lower error rate than the physical qubits composing it — and that the rate keeps improving as the code distance grows — moved it into a different category. It is now largely a manufacturing and systems-engineering problem.' ),
			array( 'h2' => 'What below-threshold means' ),
			array( 'p' => 'Quantum error correction spreads one logical qubit across many physical ones. Below threshold, adding more physical qubits to the code makes the logical error rate fall. Above threshold, it makes it rise, because the extra components introduce more errors than the code removes. Everything depends on which side of that line the hardware sits.' ),
			array( 'quote' => 'Below threshold, scale is an investment. Above threshold, scale is a liability. The line is the entire story.' ),
			array( 'h2' => 'The ratio problem' ),
			array( 'p' => 'Being below threshold is necessary but not sufficient. The open question is the overhead ratio — how many physical qubits per useful logical one — and the honest answer remains large. Estimates for commercially interesting algorithms land in ranges that imply machines substantially bigger than anything built.' ),
			array(
				'list' => array(
					'Physical qubit count: growing steadily, now in the thousands for leading systems.',
					'Logical qubits demonstrated: a handful, with convincing error suppression.',
					'Logical qubits needed for cryptographically relevant factoring: thousands, by most published estimates.',
				),
			),
			array( 'h2' => 'The unglamorous bottleneck' ),
			array( 'p' => 'Control electronics are becoming the limiting factor. Each physical qubit needs control lines, and the cabling, cooling and signal-generation requirements scale with the qubit count in ways that do not fit existing cryostat designs. Cryogenic control chips that move the electronics inside the cold stage are the current focus, and they are a semiconductor problem rather than a physics one.' ),
			array( 'h2' => 'What to watch' ),
			array( 'p' => 'The meaningful metric is not qubit count. It is logical qubit count at a stated logical error rate, sustained over a stated number of operations. Announcements that report the first number without the others are reporting a component, not a capability.' ),
		),
	),

	array(
		'slug'     => 'fusion-control-software',
		'title'    => 'Fusion research has a software problem it is only now staffing for',
		'category' => 'science',
		'tags'     => array( 'fusion', 'control-systems', 'simulation', 'real-time' ),
		'excerpt'  => 'Plasma control runs on microsecond deadlines against a system nobody can fully model. The control stack is becoming as much of a research programme as the physics.',
		'days_ago' => 14,
		'blocks'   => array(
			array( 'p' => 'A tokamak plasma is unstable on timescales shorter than a human reaction. Keeping it confined requires sensing its shape and adjusting magnetic fields continuously, within deadlines measured in microseconds, using a model of plasma behaviour that is approximate by necessity.' ),
			array( 'h2' => 'Control as a research problem' ),
			array( 'p' => 'Classical control theory handles systems you can model well. Plasma is not one of those. The dynamics are nonlinear, high-dimensional, and the relevant instabilities develop faster than any full simulation can run.' ),
			array( 'quote' => 'You cannot simulate the plasma in real time. You can only predict the specific thing about it that is about to go wrong.' ),
			array( 'p' => 'This is why learned controllers have drawn interest: a policy trained in simulation can be evaluated in microseconds even when the simulator that trained it runs far slower than real time. The approach has produced genuine results on shape control.' ),
			array( 'h2' => 'The verification gap' ),
			array( 'p' => 'A learned controller on an experimental device that can damage itself raises an uncomfortable question: what are the guarantees? The answer in practice is a layered architecture.' ),
			array(
				'list' => array(
					'A learned policy handles normal operation and optimises performance.',
					'A conventional controller with provable stability properties runs underneath as a fallback.',
					'A hardware interlock, outside the software entirely, terminates the shot on defined conditions.',
				),
			),
			array( 'p' => 'The interlock layer is deliberately simple enough to be audited by reading it. That constraint — simple enough to fully reason about — is doing more safety work than any amount of testing on the layers above.' ),
			array( 'h2' => 'Staffing' ),
			array( 'p' => 'Fusion programmes have historically hired physicists who write code rather than software engineers who learn physics. That is changing, driven by the recognition that real-time distributed systems with hard deadlines and safety interlocks are a specialist discipline. The programmes that made the hiring shift early describe it as the highest-return decision on their roadmap.' ),
		),
	),

	array(
		'slug'     => 'orbital-compute',
		'title'    => 'Orbital datacentres are a thermal argument, not a power argument',
		'category' => 'science',
		'tags'     => array( 'space', 'datacentre', 'thermal', 'latency' ),
		'excerpt'  => 'Continuous solar power in orbit is real. Getting rid of the waste heat afterwards is the part the proposals keep underestimating.',
		'days_ago' => 18,
		'blocks'   => array(
			array( 'p' => 'The case for putting compute in orbit starts with power. In a suitable orbit, a solar array produces continuously, with no night, no weather and no grid interconnection queue. For a power-constrained industry that is an attractive sentence.' ),
			array( 'h2' => 'The second half of the energy balance' ),
			array( 'p' => 'Every watt that goes into a processor comes out as heat, and in vacuum there is no air to carry it away and no water to boil. The only mechanism available is radiation, which scales with surface area and the fourth power of temperature.' ),
			array( 'quote' => 'Terrestrial datacentres cool by moving matter. Orbital ones can only cool by emitting light, and that is a much slower process.' ),
			array( 'p' => 'Practically, a radiator sized for a meaningful compute load is large — substantially larger than the solar array feeding it, because the array captures energy at the sun\'s temperature and the radiator must reject it near room temperature. Deploying and maintaining that structure is the dominant engineering cost of the whole concept.' ),
			array( 'h2' => 'The other constraints' ),
			array(
				'list' => array(
					'<strong>Radiation.</strong> Commercial silicon in orbit accumulates damage and suffers single-event upsets; radiation-tolerant parts are generations behind on performance.',
					'<strong>Maintenance.</strong> A failed component in a terrestrial rack is swapped in minutes. In orbit it is written off.',
					'<strong>Launch mass.</strong> Compute is dense, but radiators and structure are not.',
					'<strong>Latency.</strong> Fine for batch training, disqualifying for anything interactive.',
				),
			),
			array( 'h2' => 'Where the idea is least silly' ),
			array( 'p' => 'The strongest version of the proposal is narrow: processing data that is already in orbit. Earth-observation constellations generate far more imagery than their downlinks can carry, and filtering or compressing it before transmission saves bandwidth that is genuinely scarce. That is a real problem with a real orbital answer, and it does not require a datacentre — it requires a somewhat better onboard computer.' ),
			array( 'p' => 'The expansive version, where general-purpose cloud workloads migrate upward, runs into the radiator and the maintenance model, and neither has a credible answer yet.' ),
		),
	),

	array(
		'slug'     => 'eu-interoperability-rules',
		'title'    => 'Interoperability mandates are being written as API specifications',
		'category' => 'policy',
		'tags'     => array( 'regulation', 'interoperability', 'apis', 'standards' ),
		'excerpt'  => 'Regulators have moved from describing outcomes to specifying interfaces. That changes who in a company has to read the legislation.',
		'days_ago' => 16,
		'blocks'   => array(
			array( 'p' => 'Technology regulation used to describe outcomes and leave implementation to the regulated. Recent interoperability rules do something different: they specify interfaces, data formats, latency expectations and error semantics, at a level of detail that reads like an API design document.' ),
			array( 'h2' => 'Why the shift happened' ),
			array( 'p' => 'Outcome-based rules proved easy to satisfy nominally and ignore substantively. A requirement to allow data portability was met with an export that was technically complete and practically unusable — a format nobody could import, produced on a timescale that made it useless.' ),
			array( 'quote' => 'The first generation of rules asked for portability. The second generation specifies the schema, because the first generation got a zip file full of PDFs.' ),
			array( 'h2' => 'What specificity costs' ),
			array( 'p' => 'Detailed specifications age. An interface mandated in legislation is hard to revise when the underlying technology moves, and the revision cycle for law is measured in years while the revision cycle for an API is measured in months.' ),
			array( 'p' => 'The drafting response has been delegation: primary legislation sets the obligation, and a technical body maintains the specification under a faster amendment process. It is a reasonable structure, and it makes the composition of that technical body consequential in a way that is easy to overlook.' ),
			array( 'h2' => 'The compliance surface' ),
			array(
				'list' => array(
					'Interfaces must exist, be documented, and be reachable by third parties without commercial negotiation.',
					'Performance obligations apply — an interface that is technically available but rate-limited into uselessness does not comply.',
					'Changes require notice periods, which constrains ordinary deprecation practice.',
					'Security requirements apply symmetrically, so an interface cannot be refused on unexamined security grounds.',
				),
			),
			array( 'h2' => 'Who reads it now' ),
			array( 'p' => 'The practical consequence inside companies is that compliance has moved into engineering. A legal team cannot assess whether a rate limit is a legitimate operational control or a compliance failure. That judgement requires someone who understands the traffic, which means the legislation now lands on an engineering desk rather than only a legal one.' ),
		),
	),

	array(
		'slug'     => 'procurement-open-source',
		'title'    => 'Public procurement discovered that open source has an upstream',
		'category' => 'policy',
		'tags'     => array( 'procurement', 'open-source', 'funding', 'government' ),
		'excerpt'  => 'Government buyers increasingly require open-source components. Few of the resulting contracts send anything back to the projects being required.',
		'days_ago' => 20,
		'blocks'   => array(
			array( 'p' => 'Open-source preference in public procurement is now common across many jurisdictions, usually justified on vendor lock-in and auditability grounds. Both arguments are sound. The implementation has a structural gap that is becoming hard to ignore.' ),
			array( 'h2' => 'The gap' ),
			array( 'p' => 'A procurement rule that requires open-source components creates demand for those components. It does not create funding for them. The contract value goes to the integrator, and the upstream projects — frequently maintained by a small number of people, sometimes one — receive nothing and acquire a new class of demanding user.' ),
			array( 'quote' => 'The procurement rule produced obligations for maintainers and revenue for integrators. Nobody designed that outcome; it is just what the contract structure does.' ),
			array( 'h2' => 'What maintainers experience' ),
			array(
				'list' => array(
					'Security questionnaires from entities with no support relationship.',
					'Requests for long-term support commitments on projects with no funding.',
					'Compliance documentation requests — SBOMs, vulnerability disclosure policies, signed releases — that assume an organisation exists.',
					'Vulnerability reports with contractual response-time expectations attached.',
				),
			),
			array( 'p' => 'Each request is individually reasonable. Collectively they constitute a job, delivered to someone who was not offered one.' ),
			array( 'h2' => 'Mechanisms that have worked' ),
			array( 'p' => 'Several approaches have produced real money reaching real maintainers. Sovereign technology funds that grant directly to projects identified as critical infrastructure. Contract clauses requiring a defined percentage of value to flow upstream. Procurement scoring that credits bidders who employ maintainers of the components they are proposing.' ),
			array( 'p' => 'The last one is the most interesting, because it changes integrator incentives rather than adding a transfer. A firm that employs a maintainer wins more contracts, which makes employing maintainers a commercial strategy rather than a donation.' ),
			array( 'h2' => 'The measurement problem' ),
			array( 'p' => 'Identifying which projects are critical is harder than it sounds. Download counts overstate the importance of developer tooling and understate deeply embedded libraries that are vendored once and never fetched again. The dependency graph of a government system is not fully known to anyone who operates it, which is, in a roundabout way, the argument for requiring SBOMs in the first place.' ),
		),
	),

);
