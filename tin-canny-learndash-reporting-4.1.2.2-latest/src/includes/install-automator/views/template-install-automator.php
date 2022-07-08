<div class="wrap uo-install-automator">

	<div class="uo-install-automator__header">

		<h1>
			<?php esc_html_e( 'Take your site to the next level with Uncanny Automator', 'uncanny-learndash-reporting' ); ?>
		</h1>
		
		<p>

			<?php esc_html_e( 'Finding Tin Canny Reporting useful for your LearnDash site?', 'uncanny-learndash-reporting' ); ?></br>

			<?php esc_html_e( "You'll love", 'uncanny-learndash-reporting' ); ?> 

			<strong>
				<?php esc_html_e( 'Uncanny Automator.', 'uncanny-learndash-reporting' ); ?>
			</strong>

			<?php esc_html_e( 'Best of all,', 'uncanny-learndash-reporting' ); ?> 

			<strong>
				<?php esc_html_e( "it's free!", 'uncanny-learndash-reporting' ); ?>
			</strong>

		</p>

		<div class="uo-install-automator__box">

			<div class="uo-install-automator__logo">

				<img src="<?php echo esc_url( $this->get_image_url( 'uncanny-automator.svg' ) ); ?>" alt="Uncanny Automator logo" />

			</div>

			<div class="uo-install-automator__text">

				<h3><?php esc_html_e( 'Uncanny Automator', 'uncanny-learndash-reporting' ); ?></h3>

				<span><?php esc_html_e( 'By the creators of Uncanny Toolkit', 'uncanny-learndash-reporting' ); ?></span>

				<span class="uo-install-automator__rating">
					<img src="<?php echo esc_url( $this->get_image_url( 'icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
					<img src="<?php echo esc_url( $this->get_image_url( 'icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
					<img src="<?php echo esc_url( $this->get_image_url( 'icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
					<img src="<?php echo esc_url( $this->get_image_url( 'icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
					<img src="<?php echo esc_url( $this->get_image_url( 'icon-star-yellow.svg' ) ); ?>" alt="Yellow half star" /> 4.7 out of 5 stars
				</span>

			</div>

			<div class="uo-install-automator__button">
					
				<?php echo $this->get_installer()->button( 'uncanny-automator', admin_url( 'admin.php?page=uncanny-automator-dashboard' ) ); ?>

			</div>

		</div>

	</div>

	<div class="uo-install-automator__body">

		<h2>
			<?php esc_html_e( 'How it works', 'uncanny-learndash-reporting' ); ?>
		</h2>

		<p>
			<?php echo sprintf( '%1$s - <strong>%2$s</strong>', esc_html__( 'Use Uncanny Automator to connect LearnDash and Tin Canny Reporting to your favourite plugins and apps', 'uncanny-learndash-reporting' ), esc_html__( 'without hiring a developer to write custom code.', 'uncanny-learndash-reporting' ) ); ?>
		</p>

		<p>
			<?php esc_html_e( 'Here are a few examples:', 'uncanny-learndash-reporting' ); ?>
		</p>
		
			<ul class="uo-install-automator__examples">
				<li>
					<?php esc_html_e( 'When users earn a score of 50% or higher, mark a course complete.', 'uncanny-learndash-reporting' ); ?> 
					<div class="uo-install-automator__requirements">
						<label><?php esc_html_e( 'Requires', 'uncanny-learndash-reporting' ); ?></label>
						<ul>
                            <li>
                                <span class="uo-install-automator__icon"><img src="<?php echo esc_url( $this->get_image_url( 'uncanny-owl-icon.svg' ) ); ?>" alt="Tin Canny Reporting" /></span> Tin Canny Reporting</li>
							<li>
                                <span class="uo-install-automator__icon"><img src="<?php echo esc_url( $this->get_image_url( 'learndash-icon.svg' ) ); ?>" alt="Learndash icon" /></span> Learndash
                            </li>
						</ul>
					</div>
				</li>
				<li>
					<?php esc_html_e( 'When users record a Passed verb in Storyline, add a tag in ActiveCampaign.', 'uncanny-learndash-reporting' ); ?>
					<div class="uo-install-automator__requirements">
						<label><?php esc_html_e( 'Requires', 'uncanny-learndash-reporting' ); ?></label>
						<ul>
							<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( $this->get_image_url( 'uncanny-owl-icon.svg' ) ); ?>" alt="Tin Canny Reporting" /></span> Tin Canny Reporting</li>
							<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( $this->get_image_url( 'active-campaign.svg' ) ); ?>" alt="ActiveCampaign icon" /></span> ActiveCampaign</li>
						</ul>
					</div>
				</li>
				<li>
					<?php esc_html_e( 'When users achieve a Completed verb in H5P, add them to a BuddyBoss social group.', 'uncanny-learndash-reporting' ); ?>
					<div class="uo-install-automator__requirements">
						<label><?php esc_html_e( 'Requires', 'uncanny-learndash-reporting' ); ?></label>
						<ul>
							<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( $this->get_image_url( 'uncanny-owl-icon.svg' ) ); ?>" alt="Tin Canny Reporting" /></span> Tin Canny Reporting</li>
							<li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( $this->get_image_url( 'buddyboss-icon.svg' ) ); ?>" alt="BuddyBoss" /></span> BuddyBoss</li>
						</ul>
					</div>
				</li>
				<li>
					<?php esc_html_e( 'When users earn a score less than 50% in iSpring, enroll them in a remedial course.', 'uncanny-learndash-reporting' ); ?> 
					<div class="uo-install-automator__requirements">
						<label><?php esc_html_e( 'Requires', 'uncanny-learndash-reporting' ); ?></label>
						<ul>
						    <li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( $this->get_image_url( 'uncanny-owl-icon.svg' ) ); ?>" alt="Learndash icon" /></span> Tin Canny Reporting</li>
						    <li><span class="uo-install-automator__icon"><img src="<?php echo esc_url( $this->get_image_url( 'tutorlms-icon.svg' ) ); ?>" alt="Learndash icon" /></span> Tutor LMS</li>
						</ul>
					</div>
				</li>
			</ul>
	
			<div class="uo-install-automator__recipes">
				<div class="header">
					<strong><?php esc_html_e( 'Uncanny Automator', 'uncanny-learndash-reporting' ); ?></strong> <?php esc_html_e( 'supports', 'uncanny-learndash-reporting' ); ?> <a href="https://automatorplugin.com/integrations/?utm_source=uncanny_learndash_groups&utm_medium=try_automator&utm_content=all_of_the_most_popular_plugins" target="_blank"> <?php esc_html_e( 'all of the most popular WordPress plugins', 'uncanny-learndash-reporting' ); ?> <span class="external-link"><img src="<?php echo esc_url( $this->get_image_url( 'icon-link-blue.svg' ) ); ?>" alt="External link icon" /></span></a> <?php esc_html_e( "and we're adding new integrations all the time. The possibilities are limitless.", 'uncanny-learndash-reporting' ); ?>
				</div>
				<div class="triggers">
					<div class="uo-recipe-simulator">
						<div class="uo-recipe-simulator__title"><?php esc_html_e( 'Choose any combination of triggers', 'uncanny-learndash-reporting' ); ?></div>
						<div class="uo-recipe-simulator__box">
							<div class="uo-recipe-simulator__items">
							<ul>
								<li><?php esc_html_e( 'Users complete a lesson', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Users are added to a group', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Users fill out a form', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Users register for an event', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Users buy a product', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Users complete a course', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Users fail a quiz', 'uncanny-learndash-reporting' ); ?></li>
							</ul>
							</div>
						</div>
					</div>
				</div>
				<div class="actions">
					<div class="uo-recipe-simulator">
						<div class="uo-recipe-simulator__title"><?php esc_html_e( '...to initiate any combination of actions', 'uncanny-learndash-reporting' ); ?></div>
						<div class="uo-recipe-simulator__box">
							<div class="uo-recipe-simulator__items">
							<ul>
								<li><?php esc_html_e( 'Add users to a group', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Send an email', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Mark a lesson complete', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Unlock a new course', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Reset course progress', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Trigger a Zapier webhook', 'uncanny-learndash-reporting' ); ?></li>
								<li><?php esc_html_e( 'Add a tag in Infusionsoft', 'uncanny-learndash-reporting' ); ?></li>
							</ul>
							</div>
						</div>
					</div>
				</div>

				<script>

					// Global JS variable to init the JS
					window.hasRecipeSimulator = true;

				</script>

				<div class="robot">
					<span class="robot"><img src="<?php echo esc_url( $this->get_image_url( 'automator-robot.svg' ) ); ?>" alt="External link icon" /></span>
				</div>

			</div>

			<p>
				<?php esc_html_e( 'Build better experiences for your users while saving money on custom development. And save', 'uncanny-learndash-reporting' ); ?>
				<strong><?php esc_html_e( 'your', 'uncanny-learndash-reporting' ); ?></strong>
				<?php esc_html_e( 'time by automating routine tasks — all with no code.', 'uncanny-learndash-reporting' ); ?>
			</p>

			<div class="uo-install-automator__box">
				<div class="uo-install-automator__logo">
					<img src="<?php echo esc_url( $this->get_image_url( 'uncanny-automator.svg' ) ); ?>" alt="Uncanny Automator logo" />
				</div>
				<div class="uo-install-automator__text">
					<h3><?php esc_html_e( 'Uncanny Automator', 'uncanny-learndash-reporting' ); ?></h3>
					<span><?php esc_html_e( 'By the creators of Uncanny Toolkit', 'uncanny-learndash-reporting' ); ?></span>
					<span class="uo-install-automator__rating">
						<img src="<?php echo esc_url( $this->get_image_url( 'icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
						<img src="<?php echo esc_url( $this->get_image_url( 'icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
						<img src="<?php echo esc_url( $this->get_image_url( 'icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
						<img src="<?php echo esc_url( $this->get_image_url( 'icon-star-yellow.svg' ) ); ?>" alt="Yellow star" />
						<img src="<?php echo esc_url( $this->get_image_url( 'icon-star-yellow.svg' ) ); ?>" alt="Yellow half star" /> 4.7 out of 5 stars</span>
				</div>
				<div class="uo-install-automator__button">
					
					<?php echo $this->get_installer()->button( 'uncanny-automator', admin_url( 'admin.php?page=uncanny-automator-dashboard' ) ); ?>

				</div>

			</div>

	</div>

</div>
